<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$db = 'finaldb';  
$user = 'root';   
$pass = '';      

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch vehicle data
    $vehicleQuery = $conn->query("SELECT v.vehicle_id, v.vehicle_name, ST_X(v.current_location) AS lat, ST_Y(v.current_location) AS lng, v.current_load, v.status
        FROM vehicle v
        JOIN user u ON v.volunteer_id = u.user_id
        WHERE u.user_type = 'Volunteer'");
    $vehicles = $vehicleQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch requests
    $requestQuery = $conn->query("SELECT r.request_id, u.fullname AS civilian_name, i.item_name, r.quantity_requested, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM requests r
        JOIN user u ON r.civilian_id = u.user_id
        JOIN items i ON r.item_id = i.item_id
        WHERE r.status = 'Pending' OR r.status = 'Processing'");
    $requests = $requestQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch offers
    $offerQuery = $conn->query("SELECT o.offer_id, u.fullname AS civilian_name, i.item_name, o.quantity_offered, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM offers o
        JOIN user u ON o.civilian_id = u.user_id
        JOIN items i ON o.item_id = i.item_id
        WHERE o.status = 'Pending' OR o.status = 'Processing'");
    $offers = $offerQuery->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        'vehicles' => $vehicles,
        'requests' => $requests,
        'offers' => $offers
    ];

    // Send response as JSON
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
