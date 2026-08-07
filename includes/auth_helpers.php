<?php

function generateRecoveryCode()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 2; $j++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        if ($i < 2) $code .= '-';
    }
    return $code;
}

function isRecoveryLocked($conn, $userId)
{
    try {
        $stmt = $conn->prepare("SELECT lock_until FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }

        $row = $result->fetch_assoc();
        if ($row['lock_until'] === null) {
            return false;
        }

        $now = new DateTime();
        $locked_until = new DateTime($row['lock_until']);
        
        if ($now < $locked_until) {
            return true;
        } else {
            $resetStmt = $conn->prepare("UPDATE users SET lock_until = NULL, failed_attempts = 0 WHERE id = ?");
            $resetStmt->bind_param("i", $userId);
            $resetStmt->execute();
            return false;
        }
    } catch (Exception $e) {
        // Columns don't exist yet; silently fail and allow recovery attempt
        return false;
    }
}

function incrementRecoveryAttempts($conn, $userId)
{
    try {
        $stmt = $conn->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $checkStmt = $conn->prepare("SELECT failed_attempts FROM users WHERE id = ?");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['failed_attempts'] >= 5) {
            $lockTime = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $lockStmt = $conn->prepare("UPDATE users SET lock_until = ? WHERE id = ?");
            $lockStmt->bind_param("si", $lockTime, $userId);
            $lockStmt->execute();
            return 'locked';
        }
        
        return 'incremented';
    } catch (Exception $e) {
        // Columns don't exist yet; silently fail
        return false;
    }
}

function resetRecoveryAttempts($conn, $userId)
{
    try {
        $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    } catch (Exception $e) {
        // Columns don't exist yet; silently fail
        return false;
    }
}
