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

$errors = [];
$fullName = '';
$email = '';
$role = 'Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $role = trim((string)($_POST['role'] ?? ''));

    try {
        registerUser(openDatabase(), $_POST);
        header('Location: login.php?registered=1', true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errors[] = 'Unable to create account right now.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width: 560px;">
  <h1 class="mb-4">Create User Account</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
      <?php foreach ($errors as $err): ?>
        <div><?= escapeHtml($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="register.php" class="bg-white p-4 rounded shadow-sm border">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label" for="full_name">Full Name</label>
      <input class="form-control" id="full_name" name="full_name" required maxlength="120" value="<?= escapeHtml($fullName) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <input class="form-control" id="email" name="email" type="email" required maxlength="254" value="<?= escapeHtml($email) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label" for="password">Password (minimum 8 characters)</label>
      <input class="form-control" id="password" name="password" type="password" required minlength="8" maxlength="128">
    </div>

    <div class="mb-3">
      <label class="form-label" for="confirm_password">Confirm Password</label>
      <input class="form-control" id="confirm_password" name="confirm_password" type="password" required minlength="8" maxlength="128">
    </div>

    <fieldset class="mb-3">
      <legend class="col-form-label pt-0 fw-bold">Account Type</legend>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="role" id="role_student" value="Student" <?= $role === 'Student' ? 'checked' : '' ?> required>
        <label class="form-check-label" for="role_student">Student</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="role" id="role_tutor" value="Tutor" <?= $role === 'Tutor' ? 'checked' : '' ?>>
        <label class="form-check-label" for="role_tutor">Tutor</label>
      </div>
    </fieldset>

    <button type="submit" class="btn btn-primary">Create Account</button>
    <a href="login.php" class="btn btn-link">Already have an account? Log In</a>
  </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>