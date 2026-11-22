<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$user = requireLogin($pdo);

$editing = isset($_GET['edit']) && $_GET['edit'] === '1';
$error = '';
$success = !empty($_GET['updated']) ? 'Profile updated successfully.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $editing = true;
    try {
        $user = updateOwnAccount($pdo, (int)$user['user_id'], $_POST);
        $_SESSION['display_name'] = $user['full_name'];
        header('Location: profile.php?updated=1', true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to update profile.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 640px;">
  <h1 class="mb-4">My Profile</h1>

  <?php if ($success !== ''): ?>
    <div class="alert alert-success"><?= escapeHtml($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($_GET['pw_updated'])): ?>
    <div class="alert alert-success">Password updated successfully.</div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
  <?php endif; ?>

  <?php if (!$editing): ?>
    <div class="bg-white p-4 rounded shadow-sm border">
      <dl class="row mb-3">
        <dt class="col-sm-4">Name</dt>
        <dd class="col-sm-8"><?= escapeHtml($user['full_name']) ?></dd>
        <dt class="col-sm-4">Email</dt>
        <dd class="col-sm-8"><?= escapeHtml($user['email']) ?></dd>
        <dt class="col-sm-4">Role</dt>
        <dd class="col-sm-8"><?= escapeHtml($user['role']) ?> <span class="text-muted small">(read only)</span></dd>
        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8"><?= escapeHtml($user['status']) ?> <span class="text-muted small">(read only)</span></dd>
      </dl>
      <a href="profile.php?edit=1" class="btn btn-primary">Edit Profile</a>
      <a href="change-password.php" class="btn btn-outline-secondary">Change Password</a>
    </div>
  <?php else: ?>
    <form method="post" action="profile.php" class="bg-white p-4 rounded shadow-sm border">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">

      <div class="mb-3">
        <label class="form-label" for="full_name">Name</label>
        <input class="form-control" id="full_name" name="full_name" required maxlength="120" value="<?= escapeHtml($_POST['full_name'] ?? $user['full_name']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" name="email" type="email" required maxlength="254" value="<?= escapeHtml($_POST['email'] ?? $user['email']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <input class="form-control" value="<?= escapeHtml($user['role']) ?>" disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Status</label>
        <input class="form-control" value="<?= escapeHtml($user['status']) ?>" disabled>
      </div>

      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="profile.php" class="btn btn-secondary">Cancel</a>
    </form>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>