<?php
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/repositories/userPDO.php';
require_once dirname(__DIR__) . '/repositories/studentPDO.php';

// UM-01: Create User Account (Student or Tutor self-registration)
function registerUser($pdo, $input)
{
    $fullName = trim((string)($input['full_name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');
    $confirmPassword = (string)($input['confirm_password'] ?? '');
    $role = trim((string)($input['role'] ?? ''));

    $errors = [];
    if ($msg = validateFullName($fullName)) {
        $errors[] = $msg;
    }
    if ($msg = validateEmailAddress($email)) {
        $errors[] = $msg;
    }
    if ($msg = validatePasswordPair($password, $confirmPassword)) {
        $errors[] = $msg;
    }
    // Public registration cannot grant Administrator privileges
    if (!in_array($role, ['Student', 'Tutor'], true)) {
        $errors[] = 'Select Student or Tutor as the account role.';
    }
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }

    if (findUserByEmail($pdo, $email)) {
        throw new InvalidArgumentException('An account with that email address already exists.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    try {
        $userId = insertUser($pdo, $fullName, $email, $passwordHash, $role, 'Active');
        if ($role === 'Student') {
            findOrCreateStudentForUser($pdo, $userId, $fullName, $email);
        }
        $pdo->commit();
        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// UM-09: Authenticate User (Log In)
function authenticateUser($pdo, $email, $password)
{
    $normalizedEmail = strtolower(trim((string)$email));
    $rawPassword = (string)$password;

    if ($normalizedEmail === '' || $rawPassword === '') {
        return false;
    }

    $user = findUserByEmail($pdo, $normalizedEmail);
    if (!$user || $user['status'] !== 'Active' || !password_verify($rawPassword, $user['password_hash'])) {
        return false;
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        updateUserPasswordHash($pdo, (int)$user['user_id'], $newHash);
    }

    return $user;
}

// UM-04: Update Own Account (Name and Email only; Role and Status cannot be changed here)
function updateOwnAccount($pdo, $userId, $input)
{
    $user = findUserById($pdo, $userId);
    if (!$user || $user['status'] !== 'Active') {
        throw new RuntimeException('Active user account not found.');
    }

    $fullName = trim((string)($input['full_name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));

    $errors = [];
    if ($msg = validateFullName($fullName)) {
        $errors[] = $msg;
    }
    if ($msg = validateEmailAddress($email)) {
        $errors[] = $msg;
    }
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }

    $existing = findUserByEmail($pdo, $email);
    if ($existing && (int)$existing['user_id'] !== (int)$userId) {
        throw new InvalidArgumentException('Another account already uses that email address.');
    }

    $pdo->beginTransaction();
    try {
        updateUserProfile($pdo, $userId, $fullName, $email);
        syncStudentProfileByUserId($pdo, $userId, $fullName, $email);
        $pdo->commit();
        return findUserById($pdo, $userId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// UM-05: Change Password
function changeOwnPassword($pdo, $userId, $currentPassword, $newPassword, $confirmPassword)
{
    $user = findUserById($pdo, $userId);
    if (!$user || $user['status'] !== 'Active') {
        throw new RuntimeException('Active user account not found.');
    }

    if (!password_verify((string)$currentPassword, $user['password_hash'])) {
        throw new InvalidArgumentException('Current password is incorrect.');
    }

    if ($msg = validatePasswordPair($newPassword, $confirmPassword)) {
        throw new InvalidArgumentException($msg);
    }

    if (password_verify((string)$newPassword, $user['password_hash'])) {
        throw new InvalidArgumentException('New password must differ from your current password.');
    }

    $newHash = password_hash((string)$newPassword, PASSWORD_DEFAULT);
    return updateUserPasswordHash($pdo, $userId, $newHash);
}

// UM-06: Update User Role (Administrator)
function changeUserRole($pdo, $actingAdmin, $targetUserId, $newRole)
{
    if ($actingAdmin['role'] !== 'Administrator') {
        throw new RuntimeException('Unauthorized role modification.');
    }

    $allowedRoles = ['Student', 'Tutor', 'Administrator'];
    if (!in_array($newRole, $allowedRoles, true)) {
        throw new InvalidArgumentException('Invalid role selected.');
    }

    $target = findUserById($pdo, $targetUserId);
    if (!$target) {
        throw new InvalidArgumentException('Target user account not found.');
    }

    if ($target['role'] === $newRole) {
        return true;
    }

    // Prevent removing the last active Administrator
    if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && $newRole !== 'Administrator') {
        if (countActiveAdministrators($pdo) <= 1) {
            throw new InvalidArgumentException('Cannot change the role of the last active Administrator.');
        }
    }

    $pdo->beginTransaction();
    try {
        updateUserRole($pdo, $targetUserId, $newRole);
        if ($newRole === 'Student') {
            findOrCreateStudentForUser($pdo, $targetUserId, $target['full_name'], $target['email']);
        }
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// UM-07: Activate or Deactivate Account (Administrator)
function changeUserStatus($pdo, $actingAdmin, $targetUserId, $newStatus)
{
    if ($actingAdmin['role'] !== 'Administrator') {
        throw new RuntimeException('Unauthorized status modification.');
    }

    if (!in_array($newStatus, ['Active', 'Inactive'], true)) {
        throw new InvalidArgumentException('Invalid account status.');
    }

    $target = findUserById($pdo, $targetUserId);
    if (!$target) {
        throw new InvalidArgumentException('Target user account not found.');
    }

    if ($target['status'] === $newStatus) {
        return true;
    }

    // Prevent deactivating the last active Administrator
    if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && $newStatus === 'Inactive') {
        if (countActiveAdministrators($pdo) <= 1) {
            throw new InvalidArgumentException('Cannot deactivate the last active Administrator.');
        }
    }

    return updateUserStatus($pdo, $targetUserId, $newStatus);
}

// UM-08: Delete User Account (Administrator)
function deleteUserAccount($pdo, $actingAdmin, $targetUserId)
{
    if ($actingAdmin['role'] !== 'Administrator') {
        throw new RuntimeException('Unauthorized account deletion.');
    }

    $target = findUserById($pdo, $targetUserId);
    if (!$target) {
        throw new InvalidArgumentException('Target user account not found.');
    }

    if ((int)$target['user_id'] === (int)$actingAdmin['user_id']) {
        throw new InvalidArgumentException('You cannot delete your own currently signed-in Administrator account.');
    }

    if ($target['role'] === 'Administrator' && $target['status'] === 'Active') {
        if (countActiveAdministrators($pdo) <= 1) {
            throw new InvalidArgumentException('Cannot delete the last active Administrator.');
        }
    }

    // Retention policy check: block deletion if related tutoring requests exist
    $relatedRequests = countRequestsForUser($pdo, $targetUserId);
    if ($relatedRequests > 0) {
        throw new InvalidArgumentException(
            'Deletion blocked: this user has ' . $relatedRequests . ' related tutoring request(s) that must be retained.'
        );
    }

    $pdo->beginTransaction();
    try {
        deleteStudentByUserId($pdo, $targetUserId);
        $deleted = deleteUserById($pdo, $targetUserId);
        $pdo->commit();
        return $deleted;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}