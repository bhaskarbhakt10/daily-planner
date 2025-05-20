<?php
require '../config/db.php';

file_put_contents('debug.log', print_r($_POST, true));


$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json'); // always return JSON
error_reporting(E_ERROR); // suppress warnings/notices
ini_set('display_errors', 0);


// echo json_encode(['status' => 'success', 'updatedWorkload' => $updatedData]);

if (!$data || !isset($data['planning']) || !isset($data['workload'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    exit;
}

// 1. Build a map of workload users
$workloadMap = [];
foreach ($data['workload'] as &$person) {
    $workloadMap[$person['user-id']] = &$person; // Use numeric ID, no strtoupper
}


// 2. Update workload based on planning tasks
foreach ($data['planning'] as $project) {
    foreach ($project['tasks'] as $task) {
        $assigneeId = $task['assigned_to']; // user_id now
        $hours = floatval($task['hours']);

        if (isset($workloadMap[$assigneeId])) {
            $workloadMap[$assigneeId]['allocated'] += $hours;
            $workloadMap[$assigneeId]['left'] -= $hours;
            $workloadMap[$assigneeId]['Task'] += 1;
        }
    }
}


// 3. Insert into DB
$finalJson = json_encode([
    'planning' => $data['planning'],
    'workload' => array_values($workloadMap)
]);

$date = isset($data['date']) ? $data['date'] : null;

if (!$date) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Date is missing.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO daily_planning_data (data, plan_date) VALUES (?, ?)");
$stmt->bind_param("ss", $finalJson, $date);


if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'updatedWorkload' => array_values($workloadMap)
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error saving data.'
    ]);
}
?>
