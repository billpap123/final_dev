<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_unset();
    session_destroy();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error logging out: ' . $e->getMessage()]);
}
