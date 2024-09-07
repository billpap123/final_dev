<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch storage location
    $query = $conn->query("SELECT storage_location FROM storage LIMIT 1");
    $storage = $query->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        'storage' => $storage
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching storage location: ' . $e->getMessage()]);
}
?>
