<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

// Retrieve filter parameters
$categories = isset($_GET['categories']) ? json_decode($_GET['categories'], true) : [];
$only_available = isset($_GET['only_available']) ? filter_var($_GET['only_available'], FILTER_VALIDATE_BOOLEAN) : false;

// Prepare SQL query with filters
$categoryFilter = "";
if (!empty($categories)) {
    $escapedCategories = array_map(function($category) use ($con) {
        return mysqli_real_escape_string($con, $category);
    }, $categories);

    $categoryFilter = "AND c.cat_name IN ('" . implode("','", $escapedCategories) . "')";
}


$availabilityFilter = $only_available ? "AND i.quantity > 0" : "";

// Adjust SQL query to join items with item_categories
$sql = "SELECT i.item_id, i.item_name, c.cat_name AS category_name, i.quantity 
        FROM items i
        JOIN item_categories c ON i.category_id = c.cat_id
        WHERE 1=1 
        $categoryFilter 
        $availabilityFilter";

$result = mysqli_query($con, $sql);

if (!$result) {
    echo json_encode(["error" => "SQL Error: " . mysqli_error($con)]);
    mysqli_close($con);
    exit;
}

$items = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

echo json_encode(['items' => $items]);

mysqli_close($con);
?>
