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

    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Admin') {
        echo json_encode(["success" => false, "error" => "Admin not logged in"]);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['item_id']) && isset($data['quantity_needed'])) {
            $item_id = $data['item_id'];
            $quantity_needed = $data['quantity_needed'];
            $admin_id = $_SESSION['user_id']; 

            $stmt = $conn->prepare("INSERT INTO announcement (admin_id, item_id, quantity_needed, date_created) VALUES (?, ?, ?, NOW())");
            
            if ($stmt->execute([$admin_id, $item_id, $quantity_needed])) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Error executing query."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Invalid input."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid request method."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
