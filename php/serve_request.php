<?php
header("Content-Type: application/json");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['vehicle_id'], $data['request_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        exit;
    }

    $vehicle_id = $data['vehicle_id'];
    $request_id = $data['request_id'];

    $stmt = $conn->prepare("
        INSERT INTO tasks (type, vehicle_id, request_id, status, assigned_date) 
        VALUES ('Delivery', :vehicle_id, :request_id, 'Processing', NOW())
    ");
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE requests SET status = 'Processing' WHERE request_id = :request_id");
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error processing request: ' . $e->getMessage()]);
}
?>
