<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the terminal.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/repositories/userPDO.php';

function promptForValue($prompt)
{
    fwrite(STDOUT, $prompt);
    $value = fgets(STDIN);
    if ($value === false) {
        throw new RuntimeException('No input received.');
    }
    return rtrim($value, "\r\n");
}

function promptForPassword()
{
    fwrite(STDOUT, 'Password: ');
    exec('stty -echo 2>/dev/null', $output, $status);
    if ($status !== 0) {
        throw new RuntimeException('Run this script from an interactive terminal to enter the password securely.');
    }

    try {
        $password = fgets(STDIN);
    } finally {
        exec('stty echo 2>/dev/null', $output, $restoreStatus);
        fwrite(STDOUT, PHP_EOL);
        if ($restoreStatus !== 0) {
            throw new RuntimeException('Could not restore terminal echo. Restore it with "stty echo".');
        }
    }

    if ($password === false) {
        throw new RuntimeException('No password received.');
    }
    return rtrim($password, "\r\n");
}

$fullName = trim(promptForValue('Full name: '));
$email = strtolower(trim(promptForValue('Email address: ')));
$password = promptForPassword();

foreach ([
    validateFullName($fullName),
    validateEmailAddress($email),
    validatePasswordPair($password, $password)
] as $error) {
    if ($error !== '') {
        fwrite(STDERR, "Invalid input: {$error}\n");
        exit(1);
    }
}

$pdo = openDatabase();
if (findUserByEmail($pdo, $email)) {
    fwrite(STDERR, "An account with that email address already exists.\n");
    exit(1);
}

try {
    $userId = insertUser(
        $pdo,
        $fullName,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        'Administrator',
        'Active'
    );
    fwrite(STDOUT, "Active administrator account created (user ID {$userId}).\n");
} catch (PDOException $e) {
    fwrite(STDERR, "Unable to create administrator: {$e->getMessage()}\n");
    exit(1);
}
