<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $raw_data = file_get_contents("php://input");
        $data = json_decode($raw_data, true);

        if ($data === null) {
            echo json_encode(["error" => "Failed to decode JSON"]);
            exit;
        }

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if ($username === null || $password === null) {
            echo json_encode(["error" => "Username or password is missing"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT user_id, password, user_type FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];

                echo json_encode(["success" => true, "user_type" => $user['user_type']]);
            } else {
                echo json_encode(["error" => "Invalid username or password."]);
            }
        } else {
            echo json_encode(["error" => "No such user found."]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
