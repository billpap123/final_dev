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

// Execute the SQL query
$sql = "SELECT * FROM items";
$result = mysqli_query($con, $sql);

$items = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

if (empty($items)) {
    echo json_encode(["error" => "No items found"]);
} else {
    echo json_encode(['items' => $items]);
}

// Close the database connection
mysqli_close($con);
?>
