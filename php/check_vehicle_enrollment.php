<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$volunteer_id = $_SESSION['user_id'];


$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $conn->prepare("SELECT vehicle_name, ST_AsText(current_location) AS current_location FROM vehicle WHERE volunteer_id = :volunteer_id");
    $stmt->execute(['volunteer_id' => $volunteer_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode([
            'requiresEnrollment' => false,
            'vehicle' => $vehicle
        ]);
    } else {
        echo json_encode(['requiresEnrollment' => true]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error checking vehicle enrollment: ' . $e->getMessage()]);
}
