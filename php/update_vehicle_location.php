<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {

    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['lat']) || !isset($data['lng'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $lat = $data['lat'];
    $lng = $data['lng'];
    $vehicle_id = 1; 

    $stmt = $conn->prepare("UPDATE vehicle SET current_location = POINT(:lat, :lng) WHERE vehicle_id = :vehicle_id");
    $stmt->bindParam(':lat', $lat);
    $stmt->bindParam(':lng', $lng);
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update location']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
