<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $volunteer_id = $_SESSION['user_id'];

    // Delete the vehicle
    $stmt = $conn->prepare("DELETE FROM vehicle WHERE volunteer_id = :volunteer_id");
    $stmt->execute(['volunteer_id' => $volunteer_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error removing vehicle: ' . $e->getMessage()]);
}
