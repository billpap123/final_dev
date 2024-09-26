<?php
header("Content-Type: application/json");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);


$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $conn->prepare("
        SELECT 
            r.request_id, 
            r.quantity_requested, 
            r.status, 
            ST_X(v.current_location) AS vehicle_lat, 
            ST_Y(v.current_location) AS vehicle_lng, 
            u.fullname AS civilian_name 
        FROM 
            tasks t
        JOIN 
            requests r ON t.request_id = r.request_id
        JOIN 
            vehicle v ON t.vehicle_id = v.vehicle_id
        JOIN 
            user u ON r.civilian_id = u.user_id
        WHERE 
            t.status = 'Processing'
    ");
    $stmt->execute();
    $processing_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $conn->prepare("
        SELECT 
            o.offer_id, 
            o.quantity_offered, 
            o.status, 
            ST_X(v.current_location) AS vehicle_lat, 
            ST_Y(v.current_location) AS vehicle_lng, 
            u.fullname AS civilian_name 
        FROM 
            tasks t
        JOIN 
            offers o ON t.offer_id = o.offer_id
        JOIN 
            vehicle v ON t.vehicle_id = v.vehicle_id
        JOIN 
            user u ON o.civilian_id = u.user_id
        WHERE 
            t.status = 'Processing'
    ");
    $stmt->execute();
    $processing_offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    echo json_encode([
        'processing_requests' => $processing_requests,
        'processing_offers' => $processing_offers,
    ]);

} catch (PDOException $e) {
    
    echo json_encode(['error' => 'Error fetching processing tasks: ' . $e->getMessage()]);
    error_log('Error fetching processing tasks: ' . $e->getMessage()); 
}
