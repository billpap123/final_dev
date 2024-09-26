<?php
header("Content-Type: application/json");
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

    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        $stmt = $conn->prepare("
            SELECT v.vehicle_id, ST_X(v.current_location) AS lat, ST_Y(v.current_location) AS lng 
            FROM vehicle v
            JOIN user u ON u.user_id = v.volunteer_id
            WHERE u.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            echo json_encode($vehicle);
        } else {
            echo json_encode(['error' => 'Vehicle not found for the logged-in user']);
        }
    } else {
        echo json_encode(['error' => 'No user ID found in session']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching vehicle data: ' . $e->getMessage()]);
}
?>
