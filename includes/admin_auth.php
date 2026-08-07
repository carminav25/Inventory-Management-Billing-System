<?php

/**
 * Admin Authentication and Authorization Helper
 * 
 * This file contains functions to check if a user is authenticated
 * and has Admin or Super Admin role.
 */

/**
 * Check if the current user is authenticated and is an Admin or Super Admin
 * If not, redirect to login page
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }

    if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin') {
        header("Location: ../../login.php");
        exit();
    }
}

/**
 * Check if the current user is an Admin or Super Admin
 * Returns true if Admin or Super Admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Super Admin');
}

/**
 * Get the current user's ID from session
 * Returns user_id or null if not authenticated
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's role from session
 * Returns role or null if not authenticated
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get the current user's full name from session
 * Returns fullname or null if not authenticated
 */
function getCurrentUserFullName() {
    return $_SESSION['fullname'] ?? null;
}

/**
 * Get the current user's username from session
 * Returns username or null if not authenticated
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}
