<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/studentPDO.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$admin = requireLogin($pdo);
requireRole($admin, ['Administrator']);

$targetId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
}
if (!$targetId || $targetId < 1) {
    http_response_code(400);
    exit('Invalid user ID');
}

$target = findUserById($pdo, $targetId);
if (!$target) {
    http_response_code(404);
    exit('User not found');
}

$relatedCount = countRequestsForUser($pdo, $targetId);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        deleteUserAccount($pdo, $admin, $targetId);
        header('Location: manage-users.php?msg=' . urlencode('Deleted account for ' . $target['full_name']), true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to delete account.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 540px;">
  <h1 class="mb-4">Delete User Account</h1>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
  <?php endif; ?>

  <div class="bg-white p-4 rounded shadow-sm border">
    <p>Delete account for <strong><?= escapeHtml($target['full_name']) ?></strong> (<?= escapeHtml($target['email']) ?>)?</p>
    <p class="text-muted">Related tutoring requests must be checked. Accounts with existing tutoring requests cannot be permanently deleted under retention rules.</p>

    <?php if ($relatedCount > 0): ?>
      <div class="alert alert-warning">
        Deletion blocked: this user has <strong><?= (int)$relatedCount ?></strong> related tutoring request(s). Deactivate the account instead if access must be revoked.
      </div>
      <a href="admin-user-status.php?user_id=<?= (int)$target['user_id'] ?>" class="btn btn-warning">Change Status</a>
      <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
    <?php else: ?>
      <form method="post" action="admin-user-delete.php">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
        <button type="submit" class="btn btn-danger">Confirm Delete</button>
        <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>