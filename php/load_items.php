<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $jsonUrl = 'http://usidas.ceid.upatras.gr/web/2023/export.php';
    $jsonData = file_get_contents($jsonUrl);

    if ($jsonData === false) {
        echo json_encode(['error' => 'Failed to fetch JSON data from the URL']);
        exit;
    }

    $data = json_decode($jsonData, true);

    if ($data === null) {
        echo json_encode(['error' => 'Failed to decode JSON data']);
        exit;
    }

    // Insert or update category
    $insertOrUpdateCategoryQuery = "
        INSERT INTO item_categories (cat_id, cat_name) 
        VALUES (:cat_id, :cat_name) 
        ON DUPLICATE KEY UPDATE cat_name = VALUES(cat_name)"; // Update category if duplicate found
    $stmtCategory = $conn->prepare($insertOrUpdateCategoryQuery);

    foreach ($data['categories'] as $category) {
        if (!empty($category['id']) && !empty($category['category_name'])) {
            $stmtCategory->bindParam(':cat_id', $category['id'], PDO::PARAM_INT);
            $stmtCategory->bindParam(':cat_name', $category['category_name'], PDO::PARAM_STR);
            $stmtCategory->execute();
        }
    }

    // Insert or update items
    $insertOrUpdateItemQuery = "
        INSERT INTO items (item_id, item_name, category_id) 
        VALUES (:item_id, :item_name, :category_id) 
        ON DUPLICATE KEY UPDATE item_name = VALUES(item_name), category_id = VALUES(category_id)"; // Update item if duplicate found
    $stmtItem = $conn->prepare($insertOrUpdateItemQuery);

    // Insert or update item details
    $insertOrUpdateItemDetailQuery = "
        INSERT INTO item_details (it_id, detail_name, detail_value) 
        VALUES (:it_id, :detail_name, :detail_value) 
        ON DUPLICATE KEY UPDATE detail_value = VALUES(detail_value)"; // Update item details if duplicate found
    $stmtDetail = $conn->prepare($insertOrUpdateItemDetailQuery);

    foreach ($data['items'] as $item) {
        if (!empty($item['id']) && !empty($item['name']) && !empty($item['category'])) {
            $stmtItem->bindParam(':item_id', $item['id'], PDO::PARAM_INT);
            $stmtItem->bindParam(':item_name', $item['name'], PDO::PARAM_STR);
            $stmtItem->bindParam(':category_id', $item['category'], PDO::PARAM_INT);
            $stmtItem->execute();

            foreach ($item['details'] as $detail) {
                if (!empty($detail['detail_name']) && !empty($detail['detail_value'])) {
                    $stmtDetail->bindParam(':it_id', $item['id'], PDO::PARAM_INT);
                    $stmtDetail->bindParam(':detail_name', $detail['detail_name'], PDO::PARAM_STR);
                    $stmtDetail->bindParam(':detail_value', $detail['detail_value'], PDO::PARAM_STR);
                    $stmtDetail->execute();
                }
            }
        }
    }

    echo json_encode(['message' => 'Data inserted or updated successfully.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
