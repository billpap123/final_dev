<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Start the session
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Database connection details
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch past requests for the logged-in user with statuses 'Completed' or 'Cancelled'
    $sql = "SELECT r.request_id, i.item_name, r.quantity_requested, r.date_created, r.status, r.num_of_people
            FROM requests r
            JOIN items i ON r.item_id = i.item_id
            WHERE r.status IN ('Completed', 'Cancelled') AND r.civilian_id = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any requests are found
    if ($requests) {
        echo json_encode(['requests' => $requests]);
    } else {
        echo json_encode(['error' => 'No past requests found']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching requests: ' . $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
