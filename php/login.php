<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

// Check connection
if (!$con) {
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $raw_data = file_get_contents("php://input");
    // Decode the JSON data
    $data = json_decode($raw_data, true);

    // Debugging output
    if ($data === null) {
        echo json_encode(["error" => "Failed to decode JSON"]);
        exit;
    }

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    // Debugging output
    if ($username === null || $password === null) {
        echo json_encode(["error" => "Username or password is missing"]);
        exit;
    }

    // Prepare and execute the query
    $stmt = $con->prepare("SELECT user_id, password, user_type FROM user WHERE username = ?");
    if (!$stmt) {
        echo json_encode(["error" => "Prepare failed: " . $con->error]);
        exit;
    }

    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Execute failed: " . $stmt->error]);
        exit;
    }

    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $user_type);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, start a session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;

            // Return success with user type
            echo json_encode(["success" => true, "user_type" => $user_type]);
        } else {
            echo json_encode(["error" => "Invalid username or password."]);
        }
    } else {
        echo json_encode(["error" => "No such user found."]);
    }

    $stmt->close();
}
$con->close();
?>
