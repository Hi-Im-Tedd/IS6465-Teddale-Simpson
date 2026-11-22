<?php
// CLI integration & regression harness for Lab 7 (UM-01..UM-10 + UC01/UC02)
ini_set('session.use_cookies', '0');

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/services/userService.php';
require_once dirname(__DIR__) . '/services/requestService.php';

$passed = 0;
$failed = 0;

function check($condition, $label)
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "PASS: $label" . PHP_EOL;
    } else {
        $failed++;
        echo "FAIL: $label" . PHP_EOL;
    }
}

try {
    // Isolated in-memory SQLite database
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $schemaSql = file_get_contents(dirname(__DIR__) . '/database/setup-database.sql');
    // Execute schema DDL only (strip seed INSERTs for deterministic test fixtures)
    $ddlOnly = preg_replace('/^INSERT\s+OR\s+IGNORE\s+INTO\s+.*?;$/ims', '', $schemaSql);
    $pdo->exec($ddlOnly);
} catch (Throwable $e) {
    fwrite(STDERR, 'Setup failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

// --- UM-01: Create User Account ---
$studentUserId = registerUser($pdo, [
    'full_name' => 'Alex Morgan',
    'email' => 'alex@example.edu',
    'password' => 'Secret123!',
    'confirm_password' => 'Secret123!',
    'role' => 'Student'
]);
$alex = findUserById($pdo, $studentUserId);
check($alex !== false && $alex['role'] === 'Student' && $alex['status'] === 'Active', 'UM-01: Student registration creates Active account');
check($alex['password_hash'] !== 'Secret123!' && password_verify('Secret123!', $alex['password_hash']), 'UM-01: Password stored as one-way hash');
check(findStudentByUserId($pdo, $studentUserId) !== false, 'UM-01: Student registration reconciles students.user_id');

$tutorUserId = registerUser($pdo, [
    'full_name' => 'Taylor Lee',
    'email' => 'taylor@example.edu',
    'password' => 'Secret123!',
    'confirm_password' => 'Secret123!',
    'role' => 'Tutor'
]);
check(findUserById($pdo, $tutorUserId)['role'] === 'Tutor' && findStudentByUserId($pdo, $tutorUserId) === false, 'UM-01: Tutor registration creates Active Tutor without student record');

try {
    registerUser($pdo, [
        'full_name' => 'Alex Duplicate',
        'email' => 'ALEX@example.edu',
        'password' => 'Secret123!',
        'confirm_password' => 'Secret123!',
        'role' => 'Student'
    ]);
    check(false, 'UM-01: Duplicate email rejected');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-01: Duplicate email rejected');
}

try {
    registerUser($pdo, [
        'full_name' => 'Sneaky Admin',
        'email' => 'sneaky@example.edu',
        'password' => 'Secret123!',
        'confirm_password' => 'Secret123!',
        'role' => 'Administrator'
    ]);
    check(false, 'UM-01: Public Administrator registration denied');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-01: Public Administrator registration denied');
}

// Seed one Administrator directly for admin test cases
$adminId = insertUser($pdo, 'System Admin', 'admin@example.edu', password_hash('AdminPass1!', PASSWORD_DEFAULT), 'Administrator', 'Active');
$adminUser = findUserById($pdo, $adminId);

// --- UM-09: Log In ---
check(authenticateUser($pdo, 'alex@example.edu', 'Secret123!') !== false, 'UM-09: Valid login succeeds');
check(authenticateUser($pdo, 'alex@example.edu', 'WrongPass!') === false, 'UM-09: Wrong password rejected');
check(authenticateUser($pdo, 'unknown@example.edu', 'Secret123!') === false, 'UM-09: Unknown email rejected');

// --- UM-02 & UM-04: View & Update Own Account ---
$updatedAlex = updateOwnAccount($pdo, $studentUserId, [
    'full_name' => 'Alex M. Morgan',
    'email' => 'alex.morgan@example.edu',
    'role' => 'Administrator', // Attempted privilege escalation ignored
    'status' => 'Inactive'
]);
check($updatedAlex['full_name'] === 'Alex M. Morgan' && $updatedAlex['email'] === 'alex.morgan@example.edu', 'UM-04: Own name and email updated');
check($updatedAlex['role'] === 'Student' && $updatedAlex['status'] === 'Active', 'UM-04: Role and status immutable via profile edit');
check(findStudentByUserId($pdo, $studentUserId)['student_email'] === 'alex.morgan@example.edu', 'UM-04: Linked student email synchronized');

// --- UM-05: Change Password ---
try {
    changeOwnPassword($pdo, $studentUserId, 'WrongCurrent!', 'NewSecret1!', 'NewSecret1!');
    check(false, 'UM-05: Incorrect current password rejected');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-05: Incorrect current password rejected');
}
check(changeOwnPassword($pdo, $studentUserId, 'Secret123!', 'NewSecret1!', 'NewSecret1!'), 'UM-05: Password changed with valid current password');
check(authenticateUser($pdo, 'alex.morgan@example.edu', 'Secret123!') === false, 'UM-05: Old password no longer authenticates');
check(authenticateUser($pdo, 'alex.morgan@example.edu', 'NewSecret1!') !== false, 'UM-05: New password authenticates');

// --- UM-03: View and Search User Accounts ---
check(count(searchUsers($pdo, 'morgan', 'Student', 'Active')) === 1, 'UM-03: Admin search by query, role, and status');
check(count(searchUsers($pdo, 'nonexistent', '', '')) === 0, 'UM-03: Admin search returns empty array when no match');

// --- UM-06: Update User Role ---
try {
    changeUserRole($pdo, $updatedAlex, $tutorUserId, 'Administrator');
    check(false, 'UM-06: Non-admin role change denied');
} catch (RuntimeException $e) {
    check(true, 'UM-06: Non-admin role change denied');
}
try {
    changeUserRole($pdo, $adminUser, $adminId, 'Student');
    check(false, 'UM-06: Demoting last active Administrator denied');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-06: Demoting last active Administrator denied');
}

// --- Regression UC01/UC02 + Role/Ownership Enforcement ---
$secondStudentId = registerUser($pdo, [
    'full_name' => 'Priya Patel',
    'email' => 'priya@example.edu',
    'password' => 'Secret123!',
    'confirm_password' => 'Secret123!',
    'role' => 'Student'
]);
$priya = findUserById($pdo, $secondStudentId);

$futureDate = (new DateTimeImmutable('+7 days'))->format('Y-m-d');
$req1 = submitRequest($pdo, [
    'course' => 'IS 6465',
    'reason' => 'Need help understanding PHP sessions and CSRF.',
    'topics' => ['Exam review', 'Practice problems'],
    'preferreddate' => $futureDate,
    'preferredtime' => 'Morning'
], $updatedAlex);

$req2 = submitRequest($pdo, [
    'course' => 'IS 6495',
    'reason' => 'Reviewing SQL joins and foreign key rules.',
    'topics' => ['Study plan'],
    'preferreddate' => $futureDate,
    'preferredtime' => 'Evening'
], $priya);

check(count(listAuthorizedRequests($pdo, $updatedAlex)) === 1, 'Ownership: Student sees only own tutoring requests');
check(count(listAuthorizedRequests($pdo, $adminUser)) === 2, 'Authorization: Administrator sees all tutoring requests');
check(findAuthorizedRequest($pdo, $req2, $updatedAlex) === false, 'Ownership: Student cannot view peer request');
check(!reviseRequest($pdo, $req2, [
    'course' => 'IS 6495',
    'reason' => 'Unauthorized edit attempt on peer request.',
    'topics' => ['Study plan'],
    'preferreddate' => $futureDate,
    'preferredtime' => 'Evening'
], $updatedAlex), 'Ownership: Student cannot edit peer request');

// --- UM-07: Activate or Deactivate Account ---
try {
    changeUserStatus($pdo, $adminUser, $adminId, 'Inactive');
    check(false, 'UM-07: Deactivating last active Administrator denied');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-07: Deactivating last active Administrator denied');
}
check(changeUserStatus($pdo, $adminUser, $secondStudentId, 'Inactive'), 'UM-07: Admin deactivates student account');
check(authenticateUser($pdo, 'priya@example.edu', 'Secret123!') === false, 'UM-07: Inactive user cannot log in');
check(changeUserStatus($pdo, $adminUser, $secondStudentId, 'Active'), 'UM-07: Admin reactivates student account');
check(authenticateUser($pdo, 'priya@example.edu', 'Secret123!') !== false, 'UM-07: Reactivated user can log in');

// --- UM-08: Delete User Account ---
try {
    deleteUserAccount($pdo, $adminUser, $studentUserId);
    check(false, 'UM-08: Deletion blocked when user has related tutoring requests');
} catch (InvalidArgumentException $e) {
    check(true, 'UM-08: Deletion blocked when user has related tutoring requests');
}
check(findUserById($pdo, $studentUserId) !== false, 'UM-08: Blocked user account remains intact');
check(deleteUserAccount($pdo, $adminUser, $tutorUserId), 'UM-08: Account with no related requests deleted');
check(findUserById($pdo, $tutorUserId) === false, 'UM-08: Deleted account removed from database');

// --- UM-10: Log Out ---
session_start();
$_SESSION['user_id'] = $studentUserId;
$_SESSION['role'] = 'Student';
logoutSession();
check(empty($_SESSION['user_id']) && session_status() !== PHP_SESSION_ACTIVE, 'UM-10: Logout clears session data and destroys session');

echo "Results: $passed passed, $failed failed" . PHP_EOL;
exit($failed > 0 ? 1 : 0);