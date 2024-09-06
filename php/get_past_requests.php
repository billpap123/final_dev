<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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

// Fetch past requests
$sql = "SELECT request_id, item_id, quantity_requested, date_created, status, num_of_people 
        FROM requests 
        WHERE status = 'Completed'";

$result = $con->query($sql);

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
$con->close();
?>
