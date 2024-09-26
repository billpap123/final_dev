<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'finaldb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $newRequestsQuery = $conn->query("SELECT COUNT(*) AS new_requests FROM requests WHERE status = 'pending'");
    $newRequests = $newRequestsQuery->fetch(PDO::FETCH_ASSOC)['new_requests'];

    $newOffersQuery = $conn->query("SELECT COUNT(*) AS new_offers FROM offers WHERE status = 'pending'");
    $newOffers = $newOffersQuery->fetch(PDO::FETCH_ASSOC)['new_offers'];

    $completedRequestsQuery = $conn->query("SELECT COUNT(*) AS completed_requests FROM requests WHERE status = 'completed'");
    $completedRequests = $completedRequestsQuery->fetch(PDO::FETCH_ASSOC)['completed_requests'];

    $completedOffersQuery = $conn->query("SELECT COUNT(*) AS completed_offers FROM offers WHERE status = 'completed'");
    $completedOffers = $completedOffersQuery->fetch(PDO::FETCH_ASSOC)['completed_offers'];

    $response = [
        'new_requests' => $newRequests,
        'new_offers' => $newOffers,
        'completed_requests' => $completedRequests,
        'completed_offers' => $completedOffers
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
