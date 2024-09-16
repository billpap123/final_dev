<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$logged_in_user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Database connection
$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch storage data
    $storageQuery = $conn->query("SELECT ST_X(storage_location) AS lat, ST_Y(storage_location) AS lng FROM storage LIMIT 1");
    $storage = $storageQuery->fetch(PDO::FETCH_ASSOC);

    // Fetch vehicle data only for the logged-in volunteer
    $vehicleQuery = $conn->prepare("SELECT v.vehicle_id, v.vehicle_name, ST_X(v.current_location) AS lat, ST_Y(v.current_location) AS lng, v.current_load, v.status
        FROM vehicle v
        WHERE v.volunteer_id = ?");
    $vehicleQuery->execute([$logged_in_user_id]);
    $vehicles = $vehicleQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch requests (assuming you still want to see all requests)
    $requestQuery = $conn->query("SELECT r.request_id, u.fullname AS civilian_name, i.item_name, r.quantity_requested, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM requests r
        JOIN user u ON r.civilian_id = u.user_id
        JOIN items i ON r.item_id = i.item_id
        WHERE r.status = 'pending'");
    $requests = $requestQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch offers (assuming you still want to see all offers)
    $offerQuery = $conn->query("SELECT o.offer_id, u.fullname AS civilian_name, i.item_name, o.quantity_offered, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM offers o
        JOIN user u ON o.civilian_id = u.user_id
        JOIN items i ON o.item_id = i.item_id
        WHERE o.status = 'pending'");
    $offers = $offerQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch active tasks and their connections to the logged-in volunteer's vehicle (requests or offers)
    $connectionQuery = $conn->prepare("
        SELECT v.vehicle_id, ST_X(v.current_location) AS vehicle_lat, ST_Y(v.current_location) AS vehicle_lng,
               CASE 
                    WHEN t.request_id IS NOT NULL THEN ST_X(u.location) 
                    ELSE ST_X(u2.location) 
               END AS task_lat,
               CASE 
                    WHEN t.request_id IS NOT NULL THEN ST_Y(u.location) 
                    ELSE ST_Y(u2.location) 
               END AS task_lng
        FROM vehicle v
        JOIN tasks t ON v.current_task = t.task_id
        LEFT JOIN requests r ON t.request_id = r.request_id
        LEFT JOIN offers o ON t.offer_id = o.offer_id
        LEFT JOIN user u ON r.civilian_id = u.user_id
        LEFT JOIN user u2 ON o.civilian_id = u2.user_id
        WHERE v.status = 'Busy' AND v.volunteer_id = ?
    ");
    $connectionQuery->execute([$logged_in_user_id]);
    $connections = $connectionQuery->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        'storage' => $storage,
        'vehicles' => $vehicles, // Only show the logged-in volunteer's vehicle
        'requests' => $requests,
        'offers' => $offers,
        'connections' => $connections // Add connections between the volunteer's vehicle and tasks
    ];

    // Send response as JSON
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>