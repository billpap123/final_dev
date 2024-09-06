<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Admin') {
    echo json_encode(["success" => false, "error" => "Admin not logged in"]);
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['item_id']) && isset($data['quantity_needed'])) {
        $item_id = $data['item_id'];
        $quantity_needed = $data['quantity_needed'];

        // Prepare and execute the query
        $stmt = $con->prepare("INSERT INTO announcement (admin_id, item_id, quantity_needed, date_created) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            die("Prepare failed: " . $con->error);
        }

        $admin_id = $_SESSION['user_id']; // Assuming admin_id is the session user_id
        $stmt->bind_param("iii", $admin_id, $item_id, $quantity_needed);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error executing query."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid input."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}

$con->close();
?>
