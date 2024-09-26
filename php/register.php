<?php
session_start(); // Start session for managing logged-in user
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$host = "localhost";
$dbname = "finaldb";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $fullname = trim($_POST['fullname']);
        $phone_number = trim($_POST['phone_number']);
        $user_type = trim($_POST['user_type']);
        $location = trim($_POST['location']); 

        $location_parts = explode(',', $location);
        if (count($location_parts) == 2) {
            $latitude = trim($location_parts[0]);
            $longitude = trim($location_parts[1]);
        } else {
            echo json_encode(['error' => 'Location format is incorrect. Please use "latitude, longitude".']);
            exit();
        }

        $point = "POINT($latitude $longitude)";

        $sql = "INSERT INTO user (username, password, fullname, phone_number, user_type, location) 
                VALUES (:username, :password, :fullname, :phone_number, :user_type, ST_GeomFromText(:point))";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':user_type', $user_type);
        $stmt->bindParam(':point', $point);

        if ($stmt->execute()) {
            // After successful registration, set session variables to log in the user
            $user_id = $conn->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;

            echo json_encode(['success' => true, 'user_type' => $user_type]);
        } else {
            echo json_encode(['error' => 'Failed to register user: ' . $stmt->errorInfo()[2]]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
