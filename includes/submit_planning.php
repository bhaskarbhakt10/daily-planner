<?php
require '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json'); // always return JSON

if (!$data || !isset($data['planning']) || !isset($data['workload'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    exit;
}

// 1. Build a map of workload users
$workloadMap = [];
foreach ($data['workload'] as &$person) {
    $workloadMap[strtoupper($person['firstname'])] = &$person; // Use reference for updates
}

// 2. Update workload based on planning tasks
foreach ($data['planning'] as $project) {
    foreach ($project['tasks'] as $task) {
        $assignee = strtoupper($task['assigned_to']);
        $hours = floatval($task['hours']);

        if (isset($workloadMap[$assignee])) {
            $workloadMap[$assignee]['allocated'] += $hours;
            $workloadMap[$assignee]['left'] -= $hours;
            $workloadMap[$assignee]['Task'] += 1;
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
