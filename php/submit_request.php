<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

session_start(); 


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id']; 


$data = json_decode(file_get_contents("php://input"), true);


if (!isset($data['item_id']) || !isset($data['people_count'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$itemId = $data['item_id'];
$peopleCount = $data['people_count'];


$dateCreated = date('Y-m-d H:i:s');


$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $pdo->prepare("INSERT INTO requests (item_id, num_of_people, civilian_id, status, date_created) VALUES (?, ?, ?, ?, ?)");

    $status = 'Pending';

    
    if ($stmt->execute([$itemId, $peopleCount, $userId, $status, $dateCreated])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to insert data']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => "Connection failed: " . $e->getMessage()]);
}
?>
