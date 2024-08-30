<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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

// Fetch categories
$sql = "SELECT * FROM item_categories";
$result = mysqli_query($con, $sql);

$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}

if (empty($categories)) {
    echo json_encode(["error" => "No categories found"]);
} else {
    echo json_encode(['categories' => $categories]);
}

// Close the database connection
mysqli_close($con);
?>
