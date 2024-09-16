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

    // Fetch new requests count
    $newRequestsQuery = $conn->query("SELECT COUNT(*) AS new_requests FROM requests WHERE status = 'pending'");
    $newRequests = $newRequestsQuery->fetch(PDO::FETCH_ASSOC)['new_requests'];

    // Fetch new offers count
    $newOffersQuery = $conn->query("SELECT COUNT(*) AS new_offers FROM offers WHERE status = 'pending'");
    $newOffers = $newOffersQuery->fetch(PDO::FETCH_ASSOC)['new_offers'];

    // Fetch completed requests count
    $completedRequestsQuery = $conn->query("SELECT COUNT(*) AS completed_requests FROM requests WHERE status = 'completed'");
    $completedRequests = $completedRequestsQuery->fetch(PDO::FETCH_ASSOC)['completed_requests'];

    // Fetch completed offers count
    $completedOffersQuery = $conn->query("SELECT COUNT(*) AS completed_offers FROM offers WHERE status = 'completed'");
    $completedOffers = $completedOffersQuery->fetch(PDO::FETCH_ASSOC)['completed_offers'];

    // Prepare the response
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
