<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable displaying errors
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

    // Fetch vehicle ID based on the logged-in user
    $stmt = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE volunteer_id = ?");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        echo json_encode(['success' => false, 'error' => 'Vehicle not found for the logged-in user']);
        exit;
    }

    $vehicle_id = $vehicle['vehicle_id'];

    // Read the input data
    $raw_data = file_get_contents("php://input");
    $data = json_decode($raw_data, true);

    if ($data === null) {
        echo json_encode(["success" => false, "error" => "Failed to decode JSON"]);
        exit;
    }

    $request_id = $data['request_id'] ?? null;

    if ($request_id) {
        // Start a transaction to ensure data consistency
        $conn->beginTransaction();

        // Update the request status to 'Processing'
        $stmt = $conn->prepare("UPDATE requests SET status = 'Processing' WHERE request_id = ?");
        $stmt->bindParam(1, $request_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Insert the new task into the tasks table
            $stmt = $conn->prepare("INSERT INTO tasks (type, vehicle_id, request_id, status, assigned_date) VALUES ('Delivery', ?, ?, 'Processing', CURDATE())");
            $stmt->bindParam(1, $vehicle_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $request_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Commit the transaction
                $conn->commit();
                echo json_encode(["success" => true]);
            } else {
                // Rollback the transaction if task creation fails
                $conn->rollBack();
                echo json_encode(["success" => false, "error" => "Failed to create task"]);
            }
        } else {
            // Rollback the transaction if request update fails
            $conn->rollBack();
            echo json_encode(["success" => false, "error" => "Failed to update request status"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Request ID not provided"]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>
