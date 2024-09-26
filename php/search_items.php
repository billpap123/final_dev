<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

$searchTerm = isset($_GET['query']) ? $_GET['query'] : '';

if ($searchTerm == '') {
    echo json_encode(['error' => 'Invalid search term']);
    exit;
}

$sql = "SELECT item_id, item_name FROM items WHERE item_name LIKE '%$searchTerm%'";
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
