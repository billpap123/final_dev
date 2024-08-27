<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Specify allowed methods (optional)
header("Access-Control-Allow-Methods: GET, POST");

// Specify allowed headers (optional)
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

$sql = "SELECT item_id, item_name, category_id FROM items"; // Adjusted query
$result = $con->query($sql);

$items = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

if (empty($items)) {
    echo json_encode(["error" => "No items found"]);
} else {
    echo json_encode(['items' => $items]);
}

$con->close();
?>
