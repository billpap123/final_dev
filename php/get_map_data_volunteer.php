<?php 
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

    // Fetch pending requests
    $pendingRequestQuery = $conn->query("SELECT r.request_id, u.fullname AS civilian_name, i.item_name, r.quantity_requested, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM requests r
        JOIN user u ON r.civilian_id = u.user_id
        JOIN items i ON r.item_id = i.item_id
        WHERE r.status = 'Pending'");
    $pendingRequests = $pendingRequestQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch processing requests
    $processingRequestQuery = $conn->query("SELECT r.request_id, u.fullname AS civilian_name, i.item_name, r.quantity_requested, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM requests r
        JOIN user u ON r.civilian_id = u.user_id
        JOIN items i ON r.item_id = i.item_id
        WHERE r.status = 'Processing'");
    $processingRequests = $processingRequestQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending offers
    $pendingOfferQuery = $conn->query("SELECT o.offer_id, u.fullname AS civilian_name, i.item_name, o.quantity_offered, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM offers o
        JOIN user u ON o.civilian_id = u.user_id
        JOIN items i ON o.item_id = i.item_id
        WHERE o.status = 'Pending'");
    $pendingOffers = $pendingOfferQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch processing offers
    $processingOfferQuery = $conn->query("SELECT o.offer_id, u.fullname AS civilian_name, i.item_name, o.quantity_offered, ST_X(u.location) AS lat, ST_Y(u.location) AS lng
        FROM offers o
        JOIN user u ON o.civilian_id = u.user_id
        JOIN items i ON o.item_id = i.item_id
        WHERE o.status = 'Processing'");
    $processingOffers = $processingOfferQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch processing tasks and their connections to the logged-in volunteer's vehicle (requests or offers)
    $connectionQuery = $conn->prepare("
        SELECT t.task_id, v.vehicle_id, v.vehicle_name, ST_X(v.current_location) AS vehicle_lat, ST_Y(v.current_location) AS vehicle_lng,
               CASE 
                    WHEN t.request_id IS NOT NULL THEN ST_X(u.location) 
                    ELSE ST_X(u2.location) 
               END AS task_lat,
               CASE 
                    WHEN t.request_id IS NOT NULL THEN ST_Y(u.location) 
                    ELSE ST_Y(u2.location) 
               END AS task_lng,
               t.type, t.status, t.assigned_date,  -- Added t.assigned_date here
               CASE
                    WHEN t.request_id IS NOT NULL THEN r.quantity_requested
                    ELSE o.quantity_offered
               END AS quantity,
               CASE
                    WHEN t.request_id IS NOT NULL THEN 'Request'
                    ELSE 'Offer'
               END AS task_type,
               CASE
                    WHEN t.request_id IS NOT NULL THEN i.item_name  -- Fetch item_name for requests
                    ELSE i2.item_name  -- Fetch item_name for offers
               END AS item_name  -- Alias it as item_name
        FROM vehicle v
        JOIN tasks t ON v.vehicle_id = t.vehicle_id
        LEFT JOIN requests r ON t.request_id = r.request_id
        LEFT JOIN offers o ON t.offer_id = o.offer_id
        LEFT JOIN user u ON r.civilian_id = u.user_id
        LEFT JOIN user u2 ON o.civilian_id = u2.user_id
        LEFT JOIN items i ON r.item_id = i.item_id  -- Join items table for requests
        LEFT JOIN items i2 ON o.item_id = i2.item_id  -- Join items table for offers
        WHERE t.status = 'Processing' AND v.volunteer_id = ?
    ");
    $connectionQuery->execute([$logged_in_user_id]);
    $connections = $connectionQuery->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        'storage' => $storage,
        'vehicles' => $vehicles, // Only show the logged-in volunteer's vehicle
        'pending_requests' => $pendingRequests,
        'processing_requests' => $processingRequests,
        'pending_offers' => $pendingOffers,
        'processing_offers' => $processingOffers,
        'connections' => $connections // Add connections between the volunteer's vehicle and processing tasks
    ];

    // Send response as JSON
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
