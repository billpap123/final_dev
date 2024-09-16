<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Read and decode the input JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Log the received data for debugging
error_log("Received data: " . $rawData);

// Validate the input
if (!isset($data['announcement_id']) || !isset($data['quantity_offered'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$announcementId = intval($data['announcement_id']);
$quantityOffered = intval($data['quantity_offered']);
$dateCreated = date('Y-m-d H:i:s');

$con = new mysqli("localhost", "root", "", "finaldb");
if ($con->connect_error) {
    error_log("Database connection failed: " . $con->connect_error);
    echo json_encode(["error" => "Connection failed: " . $con->connect_error]);
    exit;
}

// Fetch the item_id based on the announcement_id
$itemQuery = $con->prepare("SELECT item_id FROM announcement WHERE announcement_id = ?");
if (!$itemQuery) {
    error_log("Prepare failed (fetching item_id): (" . $con->errno . ") " . $con->error);
    echo json_encode(['error' => 'Database error during prepare: ' . $con->error]);
    exit;
}

$itemQuery->bind_param("i", $announcementId);
if (!$itemQuery->execute()) {
    error_log("Execute failed (fetching item_id): (" . $itemQuery->errno . ") " . $itemQuery->error);
    echo json_encode(['error' => 'Database error during execute: ' . $itemQuery->error]);
    exit;
}

$itemQuery->bind_result($itemId);
if (!$itemQuery->fetch()) {
    echo json_encode(['error' => 'No item found for the provided announcement ID']);
    exit;
}
$itemQuery->close();

// Prepare the SQL statement with item_id included
$stmt = $con->prepare("INSERT INTO offers (civilian_id, item_id, quantity_offered, date_created, `status`) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: (" . $con->errno . ") " . $con->error);
    echo json_encode(['error' => 'Database error during prepare: ' . $con->error]);
    exit;
}

$status = 'Pending';

// Bind parameters
if (!$stmt->bind_param("iiiss", $userId, $itemId, $quantityOffered, $dateCreated, $status)) {
    error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    echo json_encode(['error' => 'Database error during bind_param: ' . $stmt->error]);
    exit;
}

// Execute the query
if (!$stmt->execute()) {
    error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    echo json_encode(['error' => 'Error during execute: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true]);

$stmt->close();
$con->close();
?>
