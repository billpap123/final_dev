<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $lat = $input['lat'];
    $lng = $input['lng'];

    // Update storage location
    $query = $conn->prepare("UPDATE storage SET storage_location = ST_GeomFromText(:point) WHERE storage_id = 1");
    $query->execute(['point' => "POINT($lat $lng)"]);

    // Prepare the response
    $response = ['success' => true];
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error updating storage location: ' . $e->getMessage()]);
}
?>
