<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Check if the user is logged in by checking if the session variable 'user_id' is set
if (isset($_SESSION['user_id'])) {
    echo json_encode(["loggedIn" => true, "user_id" => $_SESSION['user_id']]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>
