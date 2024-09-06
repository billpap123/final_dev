<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

// Check connection
if (!$con) {
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Fetch announcements
$query = "SELECT a.announcement_id, i.item_name, a.quantity_needed, a.date_created 
          FROM announcement a 
          JOIN items i ON a.item_id = i.item_id";
$result = mysqli_query($con, $query);

if ($result) {
    $announcements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = $row;
    }
    echo json_encode(['announcements' => $announcements]);
} else {
    echo json_encode(['error' => 'Error fetching announcements: ' . mysqli_error($con)]);
}

mysqli_close($con);
?>
