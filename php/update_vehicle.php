<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$volunteer_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$vehicleName = $data['vehicleName'];

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update vehicle details
    $stmt = $conn->prepare("UPDATE vehicle SET vehicle_name = :vehicle_name WHERE volunteer_id = :volunteer_id");
    $stmt->execute([
        'vehicle_name' => $vehicleName,
        'volunteer_id' => $volunteer_id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error updating vehicle: ' . $e->getMessage()]);
}
