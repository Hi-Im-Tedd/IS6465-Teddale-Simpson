<?php
require_once __DIR__ . '/auth.php';
startSecureSession();
$navLoggedIn = !empty($_SESSION['user_id']);
$navRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$navName = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="../index.php">Peer Tutoring Hub</a>
    <div class="navbar-nav me-auto">
      <?php if ($navLoggedIn): ?>
        <a class="nav-link" href="profile.php">My Profile</a>
        <?php if ($navRole === 'Student'): ?>
          <a class="nav-link" href="request-form.php">Request a Session</a>
          <a class="nav-link" href="requests.php">My Requests</a>
        <?php elseif ($navRole === 'Administrator'): ?>
          <a class="nav-link" href="requests.php">All Requests</a>
          <a class="nav-link" href="manage-users.php">Manage Users</a>
        <?php endif; ?>
      <?php else: ?>
        <a class="nav-link" href="login.php">Log In</a>
        <a class="nav-link" href="register.php">Create Account</a>
      <?php endif; ?>
    </div>
    <?php if ($navLoggedIn): ?>
      <div class="d-flex align-items-center gap-3 text-white">
        <span class="small"><?= htmlspecialchars($navName . ' (' . $navRole . ')', ENT_QUOTES, 'UTF-8') ?></span>
        <form method="post" action="logout.php" class="m-0">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn btn-outline-light btn-sm">Log Out</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</nav>