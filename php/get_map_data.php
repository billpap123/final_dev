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

    // Fetch storage data
    $storageQuery = $conn->query("SELECT ST_X(storage_location) AS lat, ST_Y(storage_location) AS lng FROM storage LIMIT 1");
    $storage = $storageQuery->fetch(PDO::FETCH_ASSOC);

    // Fetch vehicle data
    $vehicleQuery = $conn->query("SELECT v.vehicle_id, v.vehicle_name, ST_X(v.current_location) AS lat, ST_Y(v.current_location) AS lng, v.current_load, v.status
        FROM vehicle v");
    $vehicles = $vehicleQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch requests (including phone number, date created, and assigned vehicle info if applicable)
    $requestQuery = $conn->query("SELECT r.request_id, u.fullname AS civilian_name, u.phone_number, i.item_name, r.quantity_requested, r.date_created, ST_X(u.location) AS lat, ST_Y(u.location) AS lng,
        v.vehicle_name AS assigned_vehicle_name, t.assigned_date AS date_served
        FROM requests r
        JOIN user u ON r.civilian_id = u.user_id
        JOIN items i ON r.item_id = i.item_id
        LEFT JOIN tasks t ON r.request_id = t.request_id
        LEFT JOIN vehicle v ON t.vehicle_id = v.vehicle_id
        WHERE r.status = 'pending'");
    $requests = $requestQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch offers (including phone number, date created, and assigned vehicle info if applicable)
    $offerQuery = $conn->query("SELECT o.offer_id, u.fullname AS civilian_name, u.phone_number, i.item_name, o.quantity_offered, o.date_created, ST_X(u.location) AS lat, ST_Y(u.location) AS lng,
        v.vehicle_name AS assigned_vehicle_name, t.assigned_date AS date_served
        FROM offers o
        JOIN user u ON o.civilian_id = u.user_id
        JOIN items i ON o.item_id = i.item_id
        LEFT JOIN tasks t ON o.offer_id = t.offer_id
        LEFT JOIN vehicle v ON t.vehicle_id = v.vehicle_id
        WHERE o.status = 'pending'");
    $offers = $offerQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch active tasks and their connections to vehicles (requests or offers)
    $connectionQuery = $conn->query("
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
        WHERE v.status = 'Busy'
    ");
    $connections = $connectionQuery->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        'storage' => $storage,
        'vehicles' => $vehicles,
        'requests' => $requests,
        'offers' => $offers,
        'connections' => $connections // Add connections between vehicles and tasks
    ];

    // Send response as JSON
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
