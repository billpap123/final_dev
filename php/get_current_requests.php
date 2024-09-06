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

// Fetch current requests
$sql = "SELECT r.request_id, i.item_name, r.num_of_people AS people_count, r.status, r.date_created
        FROM requests r
        JOIN items i ON r.item_id = i.item_id
        WHERE r.status IN ('Pending', 'Accepted')";

$result = $con->query($sql);

$requests = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

echo json_encode(['requests' => $requests]);

// Close connection
$con->close();
?>
