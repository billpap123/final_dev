<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Start the session (if using session-based authentication)
session_start();

// Ensure user is logged in (session or other authentication)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Get the logged-in user's ID (from session or request parameter)
$user_id = $_SESSION['user_id']; // Or retrieve from request parameter

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finaldb";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $con->connect_error]);
    exit;
}

// Fetch past requests for the logged-in user
$sql = "SELECT request_id, item_id, quantity_requested, date_created, status, num_of_people 
        FROM requests 
        WHERE status = 'Completed' AND civilian_id = ?";

// Prepare and bind the statement to prevent SQL injection
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the user_id

$stmt->execute();
$result = $stmt->get_result();

$requests = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
} else {
    echo json_encode(['error' => 'No past requests found']);
    exit;
}

echo json_encode(['requests' => $requests]);

// Close connection
$stmt->close();
$con->close();
?>
