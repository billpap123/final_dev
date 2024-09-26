<?php
// Database connection
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
    die(json_encode(['message' => 'Connection failed: ' . $e->getMessage()]));
}


header('Content-Type: application/json');

try {
    
    $sql = "SELECT user_id, username, fullname, phone_number, user_type FROM user";
    $stmt = $conn->prepare($sql);
    $stmt->execute();


    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['message' => 'Error fetching users: ' . $e->getMessage()]);
}


$conn = null;
?>
