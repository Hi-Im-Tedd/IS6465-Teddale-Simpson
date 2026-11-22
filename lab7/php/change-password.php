<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$user = requireLogin($pdo);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        changeOwnPassword(
            $pdo,
            (int)$user['user_id'],
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
        session_regenerate_id(true);
        header('Location: profile.php?pw_updated=1', true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to update password.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 500px;">
  <h1 class="mb-4">Change Password</h1>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
  <?php endif; ?>

  <form method="post" action="change-password.php" class="bg-white p-4 rounded shadow-sm border">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label" for="current_password">Current Password</label>
      <input class="form-control" id="current_password" name="current_password" type="password" required>
    </div>

    <div class="mb-3">
      <label class="form-label" for="new_password">New Password (minimum 12 characters)</label>
      <input class="form-control" id="new_password" name="new_password" type="password" required minlength="12" maxlength="128">
    </div>

    <div class="mb-3">
      <label class="form-label" for="confirm_password">Confirm New Password</label>
      <input class="form-control" id="confirm_password" name="confirm_password" type="password" required minlength="12" maxlength="128">
    </div>

    <button type="submit" class="btn btn-primary">Update Password</button>
    <a href="profile.php" class="btn btn-secondary">Cancel</a>
  </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>