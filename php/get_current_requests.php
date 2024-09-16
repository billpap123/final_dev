<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finaldb";

$con = new mysqli($servername, $username, $password, $dbname);

if ($con->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $con->connect_error]);
    exit;
}

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    $con->close();
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current requests for the logged-in user
$sql = "SELECT r.request_id, i.item_name, r.num_of_people AS people_count, r.status, r.date_created
        FROM requests r
        JOIN items i ON r.item_id = i.item_id
        WHERE r.civilian_id = ? AND r.status IN ('Pending', 'Accepted')";

$stmt = $con->prepare($sql);
if ($stmt === false) {
    echo json_encode(["error" => "Prepare failed: " . $con->error]);
    $con->close();
    exit;
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    $stmt->close();
    $con->close();
    exit;
}

$result = $stmt->get_result();

$requests = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

echo json_encode(['requests' => $requests]);

// Close connection
$stmt->close();
$con->close();
?>
