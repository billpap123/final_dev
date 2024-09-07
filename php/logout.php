<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Destroy the session
    session_unset();
    session_destroy();

    // Send a JSON response indicating success
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error logging out: ' . $e->getMessage()]);
}
