<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    
    $stmt = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE volunteer_id = ?");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        echo json_encode(['success' => false, 'error' => 'Vehicle not found for the logged-in user']);
        exit;
    }

    $vehicle_id = $vehicle['vehicle_id'];

    $raw_data = file_get_contents("php://input");
    $data = json_decode($raw_data, true);

    if ($data === null) {
        echo json_encode(["success" => false, "error" => "Failed to decode JSON"]);
        exit;
    }

    $offer_id = $data['offer_id'] ?? null;

    if ($offer_id) {
        $stmt = $conn->prepare("UPDATE offers SET status = 'Cancelled' WHERE offer_id = ?");
        $stmt->bindParam(1, $offer_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE tasks SET status = 'Cancelled', completed_date = NOW() WHERE offer_id = ?");
            $stmt->bindParam(1, $offer_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to update task status"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update offer status"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Offer ID not provided"]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>
