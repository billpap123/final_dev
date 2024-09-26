<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

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
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit;
}

$categories = isset($_GET['categories']) ? json_decode($_GET['categories'], true) : [];
$only_available = isset($_GET['only_available']) ? filter_var($_GET['only_available'], FILTER_VALIDATE_BOOLEAN) : false;

$categoryFilter = "";
if (!empty($categories)) {
    $categoryPlaceholders = implode(",", array_fill(0, count($categories), "?"));
    $categoryFilter = "AND c.cat_name IN ($categoryPlaceholders)";
}

$availabilityFilter = $only_available ? "AND i.quantity > 0" : "";

$sql = "SELECT i.item_id, i.item_name, c.cat_name AS category_name, i.quantity 
        FROM items i
        JOIN item_categories c ON i.category_id = c.cat_id
        WHERE 1=1 
        $categoryFilter 
        $availabilityFilter";

try {
    $stmt = $conn->prepare($sql);

    if (!empty($categories)) {
        foreach ($categories as $index => $category) {
            $stmt->bindValue($index + 1, $category, PDO::PARAM_STR);
        }
    }

    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $items]);

} catch (PDOException $e) {
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}

$conn = null;
?>
