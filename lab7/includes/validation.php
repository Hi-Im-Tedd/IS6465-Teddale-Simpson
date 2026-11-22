<?php

function validatePreferredTime($preferredTime)
{
    $allowedTimes = ['Morning', 'Afternoon', 'Evening'];
    if (!in_array($preferredTime, $allowedTimes, true)) {
        return 'Choose a valid preferred time.';
    }
    return '';
}

function validateTopics($topics)
{
    $allowedTopics = ['Exam review', 'Practice problems', 'Study plan'];

    if ($topics === []) {
        return 'Select at least one valid topic.';
    }

    foreach ($topics as $topic) {
        if (!in_array($topic, $allowedTopics, true)) {
            return 'Select at least one valid topic.';
        }
    }

    return '';
}

function validateReason($reason)
{
    $length = mb_strlen(trim($reason));

    if ($length < 10 || $length > 500) {
        return 'Describe your request using 10 to 500 characters.';
    }

    return '';
}

function validateFullName($name)
{
    $trimmed = trim((string)$name);
    $len = mb_strlen($trimmed);
    if ($len < 2 || $len > 120) {
        return 'Full name must be between 2 and 120 characters.';
    }
    return '';
}

function validateEmailAddress($email)
{
    $normalized = strtolower(trim((string)$email));
    if ($normalized === '' || mb_strlen($normalized) > 254 || !filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }
    return '';
}

function validatePasswordPair($password, $confirmPassword)
{
    $pw = (string)$password;
    $confirm = (string)$confirmPassword;
    if (mb_strlen($pw) < 12 || mb_strlen($pw) > 128) {
        return 'Password must be between 12 and 128 characters.';
    }
    if ($pw !== $confirm) {
        return 'Password and confirmation do not match.';
    }
    return '';
}