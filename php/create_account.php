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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($data['username']) && isset($data['password'])) {
            $username = $data['username'];
            $password = $data['password'];

            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            
            $fullname = isset($data['fullname']) ? $data['fullname'] : '';
            $phone_number = isset($data['phone_number']) ? $data['phone_number'] : '';
            $location = isset($data['location']) ? $data['location'] : '';

            
            $sql = "INSERT INTO user (username, password, user_type, fullname, phone_number, location) 
                    VALUES (:username, :password, 'Volunteer', :fullname, :phone_number, :location)";
            $stmt = $conn->prepare($sql);

            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':location', $location);

            try {
                
                $stmt->execute();
                echo json_encode(['message' => 'User created successfully']);
            } catch (PDOException $e) {
                
                error_log('Error executing statement: ' . $e->getMessage());
                echo json_encode(['message' => 'Error creating user. Please try again.']);
            }
        } else {
            echo json_encode(['message' => 'Invalid data received']);
        }
    } else {
        echo json_encode(['message' => 'Invalid JSON received']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method']);
}
?>
