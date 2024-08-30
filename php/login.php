<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "finaldb");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $con->prepare("SELECT user_id, password, user_type FROM user WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
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

            // Redirect based on user type
            if ($user_type == 'Admin') {
                header("Location: ../html/admin.html");
            } elseif ($user_type == 'Volunteer') {
                header("Location: ../html/volunteer.html");
            } else {
                header("Location: ../html/civ.html");
            }
            exit();
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "No such user found.";
    }

    $stmt->close();
}
$con->close();
?>
