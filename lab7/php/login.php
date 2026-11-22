<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';

startSecureSession();
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php', true, 302);
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $user = authenticateUser(openDatabase(), $email, $password);
    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['display_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['csrf_token']);
        csrfToken();
        header('Location: profile.php', true, 303);
        exit;
    }
    $error = 'Invalid email or password.';
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 480px;">
  <h1 class="mb-4">Log In</h1>

  <?php if (!empty($_GET['registered'])): ?>
    <div class="alert alert-success">Account created and active. You may now log in.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['logged_out'])): ?>
    <div class="alert alert-info">You have been logged out.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['expired'])): ?>
    <div class="alert alert-warning">Your session expired due to inactivity. Please log in again.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['inactive'])): ?>
    <div class="alert alert-warning">Your account is inactive or unavailable.</div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
  <?php endif; ?>

  <form method="post" action="login.php" class="bg-white p-4 rounded shadow-sm border">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <input class="form-control" id="email" name="email" type="email" required value="<?= escapeHtml($email) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label" for="password">Password</label>
      <input class="form-control" id="password" name="password" type="password" required>
    </div>

    <button type="submit" class="btn btn-primary">Log In</button>
    <a href="register.php" class="btn btn-link">Create Account</a>
  </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>