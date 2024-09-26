<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);


$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {

    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

try {

    $query = "SELECT a.announcement_id, i.item_name, a.quantity_needed, a.date_created 
              FROM announcement a 
              JOIN items i ON a.item_id = i.item_id";
    $stmt = $conn->prepare($query);
    $stmt->execute();


    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode(['announcements' => $announcements]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching announcements: ' . $e->getMessage()]);
}


$conn = null;
?>
