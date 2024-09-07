<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch current user ID from session
    $volunteer_id = $_SESSION['user_id'];

    // Get vehicle details from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    $vehicleName = $data['vehicleName'];

    // Fetch current location from the user table
    $stmt = $conn->prepare("SELECT ST_AsText(location) AS location FROM user WHERE user_id = :volunteer_id");
    $stmt->execute(['volunteer_id' => $volunteer_id]);
    $location = $stmt->fetchColumn();

    if (!$location) {
        echo json_encode(['success' => false, 'error' => 'User location not found']);
        exit();
    }

    // Check if the volunteer already has a vehicle
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicle WHERE volunteer_id = :volunteer_id");
    $stmt->execute(['volunteer_id' => $volunteer_id]);
    $vehicle_count = $stmt->fetchColumn();

    if ($vehicle_count > 0) {
        // If a vehicle already exists, update the existing record
        $stmt = $conn->prepare("UPDATE vehicle 
                                SET vehicle_name = :vehicle_name, current_location = ST_GeomFromText(:location), status = 'Available' 
                                WHERE volunteer_id = :volunteer_id");
        $stmt->execute([
            'vehicle_name' => $vehicleName,
            'location' => $location,
            'volunteer_id' => $volunteer_id
        ]);
        echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully']);
    } else {
        // If no vehicle exists, insert a new one
        $stmt = $conn->prepare("INSERT INTO vehicle (volunteer_id, vehicle_name, current_location, status) 
                                VALUES (:volunteer_id, :vehicle_name, ST_GeomFromText(:location), 'Available')");
        $stmt->execute([
            'volunteer_id' => $volunteer_id,
            'vehicle_name' => $vehicleName,
            'location' => $location
        ]);
        echo json_encode(['success' => true, 'message' => 'Vehicle enrolled successfully']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error enrolling vehicle: ' . $e->getMessage()]);
}
