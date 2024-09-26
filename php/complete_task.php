<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the task_id from the POST request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['task_id'])) {
        echo json_encode(['error' => 'Missing task_id']);
        exit;
    }

    $taskId = $data['task_id'];

    // Update the task status to 'Completed' and set the completed_date
    $completeTaskQuery = $conn->prepare("UPDATE tasks SET status = 'Completed', completed_date = NOW() WHERE task_id = ?");
    $completeTaskQuery->execute([$taskId]);

    // Check if the task involves a request or an offer, and update the related table accordingly
    $taskQuery = $conn->prepare("SELECT request_id, offer_id FROM tasks WHERE task_id = ?");
    $taskQuery->execute([$taskId]);
    $task = $taskQuery->fetch(PDO::FETCH_ASSOC);

    if ($task['request_id']) {
        // It's a request, update the request status to 'Completed'
        $updateRequestQuery = $conn->prepare("UPDATE requests SET status = 'Completed' WHERE request_id = ?");
        $updateRequestQuery->execute([$task['request_id']]);
    } elseif ($task['offer_id']) {
        // It's an offer, update the offer status to 'Completed'
        $updateOfferQuery = $conn->prepare("UPDATE offers SET status = 'Completed' WHERE offer_id = ?");
        $updateOfferQuery->execute([$task['offer_id']]);
    }

    echo json_encode(['success' => true, 'message' => 'Task completed successfully']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
