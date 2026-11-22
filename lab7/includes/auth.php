<?php
require_once dirname(__DIR__) . '/repositories/userPDO.php';

function startSecureSession()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken()
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($postData)
{
    startSecureSession();
    $submitted = isset($postData['csrf_token']) && is_string($postData['csrf_token']) ? $postData['csrf_token'] : '';
    $expected = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if ($submitted === '' || $expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('Invalid or missing CSRF token.');
    }
}

function logoutSession()
{
    startSecureSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function requireLogin($pdo)
{
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php', true, 302);
        exit;
    }
    $lastActivity = isset($_SESSION['last_activity']) ? (int)$_SESSION['last_activity'] : 0;
    if (time() - $lastActivity > 1200) {
        logoutSession();
        header('Location: login.php?expired=1', true, 302);
        exit;
    }
    $user = findUserById($pdo, (int)$_SESSION['user_id']);
    if (!$user || $user['status'] !== 'Active') {
        logoutSession();
        header('Location: login.php?inactive=1', true, 302);
        exit;
    }
    $_SESSION['last_activity'] = time();
    $_SESSION['display_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    return $user;
}

function requireRole($user, $allowedRoles)
{
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('You are not authorized to perform this action.');
    }
}