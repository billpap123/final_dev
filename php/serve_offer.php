<?php
header("Content-Type: application/json");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['vehicle_id'], $data['offer_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        exit;
    }

    $vehicle_id = $data['vehicle_id'];
    $offer_id = $data['offer_id'];

    $stmt = $conn->prepare("
        INSERT INTO tasks (type, vehicle_id, offer_id, status, assigned_date) 
        VALUES ('Pickup', :vehicle_id, :offer_id, 'Processing', NOW())
    ");
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->bindParam(':offer_id', $offer_id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE offers SET status = 'Processing' WHERE offer_id = :offer_id");
    $stmt->bindParam(':offer_id', $offer_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error processing offer: ' . $e->getMessage()]);
}
