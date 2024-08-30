<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection using mysqli_connect
$con = mysqli_connect("localhost", "root", "", "finaldb");

if (!$con) {
    die(json_encode(["message" => "Connection failed: " . mysqli_connect_error()]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $fullname = $_POST['fullname'];
    $phone_number = $_POST['phone_number'];
    $user_type = $_POST['user_type'];
    $location = $_POST['location']; // e.g., "latitude, longitude"

    // Validate and split location into latitude and longitude
    $location_parts = explode(', ', $location);
    if (count($location_parts) == 2) {
        $latitude = trim($location_parts[0]);
        $longitude = trim($location_parts[1]);
    } else {
        die("Error: Location format is incorrect. Please use 'latitude, longitude'.");
    }

    // Prepare the POINT value as a WKT string
    $point = "POINT($latitude $longitude)";

    // Prepare SQL statement
    $sql = "INSERT INTO user (username, password, fullname, phone_number, user_type, location) 
            VALUES (?, ?, ?, ?, ?, ST_GeomFromText(?))";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        die("Error preparing the statement: " . $con->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssss", $username, $password, $fullname, $phone_number, $user_type, $point);

    // Execute and check for errors
    if ($stmt->execute()) {
        echo "Registration successful!";
        // Redirect based on user type
        if ($user_type == 'Admin') {
            header("Location: ../html/admin.html");
        } elseif ($user_type == 'Volunteer') {
            header("Location: ../html/volunteer.html");
        } else {
            header("Location: ../html/civilian.html");
        }
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>
