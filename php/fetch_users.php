<?php
// Database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    die(json_encode(["message" => "Connection failed: " . mysqli_connect_error()]));
}

// Set JSON content type header
header('Content-Type: application/json');

// Query to fetch all users
$sql = "SELECT user_id, username, fullname, phone_number, user_type FROM user";
$result = mysqli_query($con, $sql);

if ($result) {
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode(['message' => 'Error fetching users']);
}

// Close the database connection
mysqli_close($con);
?>
