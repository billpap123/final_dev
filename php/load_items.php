<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";

// Load JSON data from file
$jsonUrl = 'http://usidas.ceid.upatras.gr/web/2023/export.php';
$jsonData = file_get_contents($jsonUrl);

if ($jsonData === false) {
    die('Failed to fetch JSON data from the URL');
}

// Decode JSON data into PHP array
$data = json_decode($jsonData, true);

// Define the SQL query to insert data into the item_categories table
$insert_item_category_query = "INSERT INTO item_categories (cat_id, cat_name) VALUES (?, ?)";

// Prepare the category statement
$stmt_category = $con->prepare($insert_item_category_query);

// Check if preparing the category statement succeeded
if ($stmt_category) {
    try {
        // Iterate over the categories in the JSON data
        foreach ($data['categories'] as $category) {
            // Bind parameters for the category statement
            $stmt_category->bind_param("is", $category['id'], $category['category_name']);

            // Execute the category statement
            $stmt_category->execute();
        }

        // Close the category statement
        $stmt_category->close();
    
        // Define the SQL query to insert data into the items table
        $insert_item_query = "INSERT INTO items (item_id, item_name, category_id) VALUES (?, ?, ?)";

        // Define the SQL query to insert data into the item_details table
        $insert_item_detail_query = "INSERT INTO item_details (it_id, detail_name, detail_value) VALUES (?, ?, ?)";

        // Prepare statements
        $stmt_item = $con->prepare($insert_item_query);
        $stmt_detail = $con->prepare($insert_item_detail_query);

        // Check if preparing statements succeeded
        if ($stmt_item && $stmt_detail) {
            // Iterate over the items in the JSON data
            foreach ($data['items'] as $item) {
                // Bind parameters for the item statement
                $stmt_item->bind_param("isi", $item['id'], $item['name'], $item['category']);

                // Execute the item statement
                $stmt_item->execute();

                // Iterate over the item details in the JSON data
                foreach ($item['details'] as $detail) {
                    // Bind parameters for the detail statement
                    $stmt_detail->bind_param("iss", $item['id'], $detail['detail_name'], $detail['detail_value']);
                    $stmt_detail->execute();
                }
            }

            echo "Data inserted successfully.";
        } else {
            echo "Prepare statement error: " . $con->error;
        }

        // Close statements and connection
        $stmt_item->close();
        $stmt_detail->close();
        $con->close();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
        // Log the error to a file or database for further investigation
    }
} else {
    echo "Prepare statement error: " . $con->error;
}
?>
