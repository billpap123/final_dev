<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id']; // Get the user ID from the session

// Read and decode the input JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['item_id']) || !isset($data['people_count'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$itemId = $data['item_id'];
$peopleCount = $data['people_count'];

// Get current date and time
$dateCreated = date('Y-m-d H:i:s');

// Database connection
$con = new mysqli("localhost", "root", "", "finaldb");

// Check connection
if ($con->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $con->connect_error]);
    exit;
}

// Prepare and bind parameters to prevent SQL injection
$stmt = $con->prepare("INSERT INTO requests (item_id, num_of_people, civilian_id, status, date_created) VALUES (?, ?, ?, ?, ?)");
$status = 'Pending';
$stmt->bind_param("iiiss", $itemId, $peopleCount, $userId, $status, $dateCreated);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => "Error: " . $stmt->error]);
}

// Close connections
$stmt->close();
$con->close();
?>
