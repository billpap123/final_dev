<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';

if ($categoryId == '') {
    echo json_encode(['error' => 'Invalid category']);
    exit;
}

// Fetch items by category
$sql = "SELECT item_id, item_name FROM items WHERE category_id = '$categoryId'";
$result = mysqli_query($con, $sql);

$items = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

echo json_encode(['items' => $items]);

mysqli_close($con);
?>
