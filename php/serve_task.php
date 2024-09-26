<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

session_start();

$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    // Ensure the user is a volunteer and has a vehicle
    $user_id = $_SESSION['user_id'];
    $vehicleQuery = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE volunteer_id = ?");
    $vehicleQuery->execute([$user_id]);
    $vehicle = $vehicleQuery->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        echo json_encode(['error' => 'Vehicle ID not found. Please ensure you are logged in.']);
        exit;
    }

    // Get the vehicle_id from the query
    $vehicle_id = $vehicle['vehicle_id'];

    // Get the task type and task ID from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['task_type']) || !isset($data['task_id'])) {
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $task_type = $data['task_type'];  // 'offer' or 'request'
    $task_id = $data['task_id'];
    $assigned_date = date('Y-m-d');

    // 1. Update the request or offer status to 'Processing'
    if ($task_type === 'request') {
        $updateRequestStatus = $conn->prepare("UPDATE requests SET status = 'Processing' WHERE request_id = ?");
        $updateRequestStatus->execute([$task_id]);
    } elseif ($task_type === 'offer') {
        $updateOfferStatus = $conn->prepare("UPDATE offers SET status = 'Processing' WHERE offer_id = ?");
        $updateOfferStatus->execute([$task_id]);
    } else {
        echo json_encode(['error' => 'Invalid task type']);
        exit;
    }

    // 2. Create a new task in the `tasks` table
    if ($task_type === 'request') {
        $insertTask = $conn->prepare("INSERT INTO tasks (type, vehicle_id, request_id, status, assigned_date) VALUES ('Delivery', ?, ?, 'Processing', ?)");
        $insertTask->execute([$vehicle_id, $task_id, $assigned_date]);
    } elseif ($task_type === 'offer') {
        $insertTask = $conn->prepare("INSERT INTO tasks (type, vehicle_id, offer_id, status, assigned_date) VALUES ('Pickup', ?, ?, 'Processing', ?)");
        $insertTask->execute([$vehicle_id, $task_id, $assigned_date]);
    }

    // Get the task_id of the newly created task
    $newTaskId = $conn->lastInsertId();

    // 3. Update the vehicle table with the `task_id` in `current_task`
    $updateVehicle = $conn->prepare("UPDATE vehicle SET current_task = ?, status = 'Busy' WHERE vehicle_id = ?");
    $updateVehicle->execute([$newTaskId, $vehicle_id]);

    echo json_encode(['success' => true, 'task_id' => $newTaskId]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
