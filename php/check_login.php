<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (isset($_SESSION['user_id'])) {
    echo json_encode(["loggedIn" => true, "user_id" => $_SESSION['user_id']]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>
