<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$admin = requireLogin($pdo);
requireRole($admin, ['Administrator']);

$q = trim((string)($_GET['q'] ?? ''));
$role = (string)($_GET['role'] ?? '');
$status = (string)($_GET['status'] ?? '');

if ($role !== '' && !in_array($role, ['Student', 'Tutor', 'Administrator'], true)) {
    http_response_code(400);
    exit('Invalid role filter');
}
if ($status !== '' && !in_array($status, ['Active', 'Inactive'], true)) {
    http_response_code(400);
    exit('Invalid status filter');
}

$users = searchUsers($pdo, $q, $role, $status);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
  <h1 class="mb-4">Manage Users</h1>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?= escapeHtml($_GET['msg']) ?></div>
  <?php endif; ?>

  <form method="get" action="manage-users.php" class="row g-2 mb-4">
    <div class="col-md-5">
      <input class="form-control" name="q" placeholder="Search by name or email" value="<?= escapeHtml($q) ?>">
    </div>
    <div class="col-md-3">
      <select class="form-select" name="role">
        <option value="">All Roles</option>
        <?php foreach (['Student', 'Tutor', 'Administrator'] as $r): ?>
          <option value="<?= escapeHtml($r) ?>" <?= $r === $role ? 'selected' : '' ?>><?= escapeHtml($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="">All Statuses</option>
        <?php foreach (['Active', 'Inactive'] as $st): ?>
          <option value="<?= escapeHtml($st) ?>" <?= $st === $status ? 'selected' : '' ?>><?= escapeHtml($st) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Search</button>
    </div>
  </form>

  <?php if (!$users): ?>
    <p>No matching user accounts found.</p>
  <?php else: ?>
    <table class="table table-striped bg-white">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Manage</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['user_id'] ?></td>
            <td><?= escapeHtml($u['full_name']) ?></td>
            <td><?= escapeHtml($u['email']) ?></td>
            <td><?= escapeHtml($u['role']) ?></td>
            <td><?= escapeHtml($u['status']) ?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="admin-user-role.php?user_id=<?= (int)$u['user_id'] ?>">Role</a>
              <a class="btn btn-sm btn-outline-warning" href="admin-user-status.php?user_id=<?= (int)$u['user_id'] ?>">Status</a>
              <a class="btn btn-sm btn-outline-danger" href="admin-user-delete.php?user_id=<?= (int)$u['user_id'] ?>">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>