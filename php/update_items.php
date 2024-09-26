<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['item_id']) && isset($data['quantity'])) {
        $item_ids = $data['item_id'];
        $quantities = $data['quantity'];

        // Ensure arrays are of the same length
        if (count($item_ids) !== count($quantities)) {
            echo json_encode(["error" => "Mismatch between item IDs and quantities"]);
            exit();
        }

        // Prepare SQL statement for updating quantities
        $query = "UPDATE items SET quantity = ? WHERE item_id = ?";
        $stmt = $pdo->prepare($query);

        foreach ($item_ids as $index => $item_id) {
            $quantity = $quantities[$index];

            // Check for valid data
            if (!is_numeric($quantity) || !is_numeric($item_id)) {
                echo json_encode(["error" => "Invalid data type for quantity or item ID"]);
                exit();
            }

            // Execute the update statement
            if (!$stmt->execute([$quantity, $item_id])) {
                echo json_encode(["error" => "Query failed: " . implode(", ", $stmt->errorInfo())]);
                exit();
            }
        }

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Missing required parameters"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
