<?php
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/repositories/studentPDO.php';
require_once dirname(__DIR__) . '/repositories/requestPDO.php';

function escapeHtml($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Week 4 form fields map to the schema without adding database columns.
// topic contains JSON {reason,topics}; preferred_time stores Morning/Afternoon/Evening.
function decodeTopic($topic)
{
    $v = json_decode($topic, true);
    return is_array($v) && isset($v['reason']) && is_array($v['topics'] ?? null)
        ? $v
        : ['reason' => $topic, 'topics' => []];
}

function validateSubmission($input, $requireStudent = true)
{
    $errors = [];
    $courses = ['IS 6465', 'IS 6495', 'IS 6570'];
    $name = trim((string)($input['student_name'] ?? ''));
    $email = strtolower(trim((string)($input['student_email'] ?? '')));
    $course = trim((string)($input['course'] ?? ''));
    $reason = trim((string)($input['reason'] ?? ''));
    $date = (string)($input['preferreddate'] ?? '');
    $time = (string)($input['preferredtime'] ?? '');
    $topics = $input['topics'] ?? [];

    if (!is_array($topics)) {
        $errors[] = 'Topics must be a list.';
        $topics = [];
    }
    if ($requireStudent && $name === '') {
        $errors[] = 'Student name is required.';
    }
    if ($requireStudent && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid student email required.';
    }
    if (!in_array($course, $courses, true)) {
        $errors[] = 'Select a valid course.';
    }
    if ($msg = validatePreferredTime($time)) {
        $errors[] = $msg;
    }
    if ($msg = validateTopics($topics)) {
        $errors[] = $msg;
    }
    if ($msg = validateReason($reason)) {
        $errors[] = $msg;
    }
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$dt || $dt->format('Y-m-d') !== $date || $dt < new DateTimeImmutable('today')) {
        $errors[] = 'Choose a valid date today or later.';
    }

    $payload = [
        'student_name' => $name,
        'student_email' => $email,
        'course' => $course,
        'topic' => json_encode(['reason' => $reason, 'topics' => array_values($topics)]),
        'preferred_date' => $date,
        'preferred_time' => $time
    ];
    return [$errors, $payload];
}

function submitRequest($pdo, $input, $user = null)
{
    if ($user !== null) {
        if ($user['role'] !== 'Student') {
            throw new RuntimeException('Only students may submit tutoring requests.');
        }
        $input['student_name'] = $user['full_name'];
        $input['student_email'] = $user['email'];
    }

    [$errors, $data] = validateSubmission($input);
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }

    $pdo->beginTransaction();
    try {
        if ($user !== null) {
            $student = findOrCreateStudentForUser($pdo, (int)$user['user_id'], $data['student_name'], $data['student_email']);
        } else {
            $student = findStudentByEmail($pdo, $data['student_email']);
            if (!$student) {
                $stmt = $pdo->prepare('INSERT INTO students(student_name,student_email) VALUES(?,?)');
                $stmt->execute([$data['student_name'], $data['student_email']]);
                $student = findStudentByEmail($pdo, $data['student_email']);
            }
        }
        if (!$student) {
            throw new RuntimeException('Student lookup failed');
        }
        $id = insertTutoringRequest($pdo, (int)$student['student_id'], $data);
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function findAuthorizedRequest($pdo, $id, $user)
{
    if ($user['role'] === 'Administrator') {
        return findRequest($pdo, $id);
    }
    if ($user['role'] === 'Student') {
        return findRequestForUser($pdo, $id, (int)$user['user_id']);
    }
    return false;
}

function listAuthorizedRequests($pdo, $user, $course = '', $status = '')
{
    if ($user['role'] === 'Administrator') {
        return listRequests($pdo, $course, $status, null);
    }
    if ($user['role'] === 'Student') {
        return listRequests($pdo, $course, $status, (int)$user['user_id']);
    }
    return [];
}

function reviseRequest($pdo, $id, $input, $user = null)
{
    [$errors, $data] = validateSubmission($input, false);
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }
    if ($user === null || $user['role'] === 'Administrator') {
        return updateOpenRequest($pdo, $id, $data);
    }
    if ($user['role'] === 'Student') {
        return updateOpenRequestForUser($pdo, $id, (int)$user['user_id'], $data);
    }
    return false;
}

function cancelAuthorizedRequest($pdo, $id, $user)
{
    if ($user['role'] === 'Administrator') {
        return cancelOpenRequest($pdo, $id);
    }
    if ($user['role'] === 'Student') {
        return cancelOpenRequestForUser($pdo, $id, (int)$user['user_id']);
    }
    return false;
}