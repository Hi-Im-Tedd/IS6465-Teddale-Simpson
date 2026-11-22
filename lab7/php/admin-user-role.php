<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repositories/userPDO.php';
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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        changeUserRole($pdo, $admin, $targetId, (string)($_POST['role'] ?? ''));
        header('Location: manage-users.php?msg=' . urlencode('Role updated for ' . $target['full_name']), true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to update role.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 520px;">
  <h1 class="mb-4">Update User Role</h1>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
  <?php endif; ?>

  <div class="bg-white p-4 rounded shadow-sm border">
    <p><strong>User:</strong> <?= escapeHtml($target['full_name']) ?> (<?= escapeHtml($target['email']) ?>)</p>
    <p><strong>Current role:</strong> <?= escapeHtml($target['role']) ?></p>

    <form method="post" action="admin-user-role.php">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
      <input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">

      <div class="mb-3">
        <label class="form-label" for="role">New Role</label>
        <select class="form-select" id="role" name="role">
          <?php foreach (['Student', 'Tutor', 'Administrator'] as $r): ?>
            <option value="<?= escapeHtml($r) ?>" <?= $r === $target['role'] ? 'selected' : '' ?>><?= escapeHtml($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Save Role</button>
      <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>