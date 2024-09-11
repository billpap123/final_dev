<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in.');
    }

    $user_id = $_SESSION['user_id'];

    $query = "
        SELECT 
            ST_X(v.current_location) AS lat, 
            ST_Y(v.current_location) AS lng 
        FROM 
            vehicle AS v
        JOIN 
            user AS u 
        ON 
            v.volunteer_id = u.user_id 
        WHERE 
            u.user_id = :user_id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['error' => 'Vehicle position not found.']);
        exit;
    }

    echo json_encode($data);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
