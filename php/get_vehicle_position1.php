<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $vehicle_id = 1; 

    
    $stmt = $conn->prepare("SELECT ST_X(current_location) AS lat, ST_Y(current_location) AS lng FROM vehicle WHERE vehicle_id = :vehicle_id");
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode($vehicle);
    } else {
        echo json_encode(['error' => 'Vehicle position not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
