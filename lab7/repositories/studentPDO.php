<?php

function findStudentByEmail($pdo, $email)
{
    $stmt = $pdo->prepare('SELECT student_id, student_name, student_email, user_id FROM students WHERE student_email = ?');
    $stmt->execute([strtolower(trim((string)$email))]);
    return $stmt->fetch();
}

function findStudentByUserId($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT student_id, student_name, student_email, user_id FROM students WHERE user_id = ?');
    $stmt->execute([(int)$userId]);
    return $stmt->fetch();
}

function findOrCreateStudentForUser($pdo, $userId, $name, $email)
{
    $normalizedEmail = strtolower(trim((string)$email));
    $cleanName = trim((string)$name);

    $existingByUser = findStudentByUserId($pdo, $userId);
    if ($existingByUser) {
        return $existingByUser;
    }

    $existingByEmail = findStudentByEmail($pdo, $normalizedEmail);
    if ($existingByEmail) {
        $stmt = $pdo->prepare('UPDATE students SET user_id = ?, student_name = ? WHERE student_id = ?');
        $stmt->execute([(int)$userId, $cleanName, (int)$existingByEmail['student_id']]);
        return findStudentByUserId($pdo, $userId);
    }

    $stmt = $pdo->prepare('INSERT INTO students(student_name, student_email, user_id) VALUES(?, ?, ?)');
    $stmt->execute([$cleanName, $normalizedEmail, (int)$userId]);
    return findStudentByUserId($pdo, $userId);
}

function syncStudentProfileByUserId($pdo, $userId, $name, $email)
{
    $stmt = $pdo->prepare('UPDATE students SET student_name = ?, student_email = ? WHERE user_id = ?');
    $stmt->execute([trim((string)$name), strtolower(trim((string)$email)), (int)$userId]);
}

function countRequestsForUser($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM tutoring_requests r
         JOIN students s ON r.student_id = s.student_id
         WHERE s.user_id = ?'
    );
    $stmt->execute([(int)$userId]);
    return (int)$stmt->fetchColumn();
}

function deleteStudentByUserId($pdo, $userId)
{
    $stmt = $pdo->prepare('DELETE FROM students WHERE user_id = ?');
    $stmt->execute([(int)$userId]);
}