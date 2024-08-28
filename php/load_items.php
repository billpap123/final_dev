<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(['error' => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

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

$insert_item_category_query = "INSERT INTO item_categories (cat_id, cat_name) VALUES (?, ?)";
$stmt_category = $con->prepare($insert_item_category_query);

if ($stmt_category) {
    foreach ($data['categories'] as $category) {
        if (!empty($category['id']) && !empty($category['category_name'])) {
            $stmt_category->bind_param("is", $category['id'], $category['category_name']);
            $stmt_category->execute();
        }
    }
    $stmt_category->close();
    
    $insert_item_query = "INSERT INTO items (item_id, item_name, category_id) VALUES (?, ?, ?)";
    $insert_item_detail_query = "INSERT INTO item_details (it_id, detail_name, detail_value) VALUES (?, ?, ?)";
    
    $stmt_item = $con->prepare($insert_item_query);
    $stmt_detail = $con->prepare($insert_item_detail_query);

    if ($stmt_item && $stmt_detail) {
        foreach ($data['items'] as $item) {
            if (!empty($item['id']) && !empty($item['name']) && !empty($item['category'])) {
                $stmt_item->bind_param("isi", $item['id'], $item['name'], $item['category']);
                $stmt_item->execute();

                foreach ($item['details'] as $detail) {
                    if (!empty($detail['detail_name']) && !empty($detail['detail_value'])) {
                        $stmt_detail->bind_param("iss", $item['id'], $detail['detail_name'], $detail['detail_value']);
                        $stmt_detail->execute();
                    }
                }
            }
        }

        echo json_encode(['message' => 'Data inserted successfully.']);
    } else {
        echo json_encode(['error' => 'Prepare statement error: ' . $con->error]);
    }

    $stmt_item->close();
    $stmt_detail->close();
    $con->close();
} else {
    echo json_encode(['error' => 'Prepare statement error: ' . $con->error]);
}
?>
