<?php
require '../config/db.php';

// file_put_contents('debug.log', print_r($_POST, true));
file_put_contents('debug.log', "Raw input: " . file_get_contents("php://input"));

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

$data = json_decode(file_get_contents("php://input"), true);




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


// 3. Insert or update DB
$finalJson = json_encode([
    'planning' => $data['planning'],
    'workload' => array_values($workloadMap)
]);

$date = $data['date'] ?? null;

$parsed = DateTime::createFromFormat('Y-m-d', $date);
if (!$parsed || $parsed->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format.']);
    exit;
}

if (!$date) {
    file_put_contents('debug.log', "Date is null or missing: " . print_r($data, true));
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Date is missing.']);
    exit;
}

// ✅ Check if a record exists for that date
$stmtCheck = $conn->prepare("SELECT id FROM daily_planning_data WHERE plan_date = ?");
$stmtCheck->bind_param("s", $date);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result && $result->num_rows > 0) {
    // ✅ UPDATE existing
    $stmt = $conn->prepare("UPDATE daily_planning_data SET data = ? WHERE plan_date = ?");
    $stmt->bind_param("ss", $finalJson, $date);
} else {
    // ✅ INSERT new
    $stmt = $conn->prepare("INSERT INTO daily_planning_data (data, plan_date) VALUES (?, ?)");
    $stmt->bind_param("ss", $finalJson, $date);
}


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