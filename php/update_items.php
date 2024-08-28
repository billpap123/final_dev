<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category']) && isset($_POST['quantity']) && isset($_POST['item_id'])) {
        $categories = $_POST['category'];
        $quantities = $_POST['quantity'];
        $item_ids = $_POST['item_id'];

        // Validate input arrays
        if (!is_array($categories) || !is_array($quantities) || !is_array($item_ids) ||
            count($categories) !== count($quantities) || count($categories) !== count($item_ids)) {
            echo json_encode(["error" => "Invalid input data"]);
            exit();
        }

        $stmt = $con->prepare("UPDATE items SET category_id = ?, quantity = ? WHERE item_id = ?");
        if (!$stmt) {
            echo json_encode(["error" => "Prepare statement error: " . $con->error]);
            exit();
        }

        foreach ($categories as $index => $category) {
            $quantity = $quantities[$index];
            $item_id = $item_ids[$index];

            // Check for valid data
            if (!is_numeric($quantity) || !is_numeric($item_id) || !is_numeric($category)) {
                echo json_encode(["error" => "Invalid data type for quantity, category, or item ID"]);
                exit();
            }

            $stmt->bind_param("iii", $category, $quantity, $item_id);
            if (!$stmt->execute()) {
                echo json_encode(["error" => "Query failed: " . $stmt->error]);
                exit();
            }
        }

        $stmt->close();
        $con->close();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Missing required parameters"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
