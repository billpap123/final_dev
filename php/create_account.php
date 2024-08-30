<?php
// Database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    die(json_encode(["message" => "Connection failed: " . mysqli_connect_error()]));
}

// Set JSON content type header
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Receive and decode JSON data
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($data['username']) && isset($data['password'])) {
            $username = mysqli_real_escape_string($con, $data['username']);
            $password = mysqli_real_escape_string($con, $data['password']);

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Handle fields that must not be NULL
            $fullname = isset($data['fullname']) ? mysqli_real_escape_string($con, $data['fullname']) : '';
            $phone_number = isset($data['phone_number']) ? mysqli_real_escape_string($con, $data['phone_number']) : '';
            $location = isset($data['location']) ? mysqli_real_escape_string($con, $data['location']) : '';

            // Prepare and execute the SQL statement
            $stmt = $con->prepare("INSERT INTO user (username, password, user_type, fullname, phone_number, location) VALUES (?, ?, 'Volunteer', ?, ?, ?)");

            if ($stmt === false) {
                die(json_encode(['message' => 'Failed to prepare the statement: ' . $con->error]));
            }

            $stmt->bind_param("sssss", $username, $hashedPassword, $fullname, $phone_number, $location);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'User created successfully']);
            } else {
                // Log the detailed error for debugging
                error_log('Error executing statement: ' . $stmt->error);
                echo json_encode(['message' => 'Error creating user. Please try again.']);
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            echo json_encode(['message' => 'Invalid data received']);
        }
    } else {
        echo json_encode(['message' => 'Invalid JSON received']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method']);
}

// Close the database connection
$con->close();
?>
