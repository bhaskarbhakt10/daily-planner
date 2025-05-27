<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!$data || !isset($data['planning']) || !isset($data['workload'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    exit;
}

// 1. Map workloads
$workloadMap = [];
foreach ($data['workload'] as &$person) {
    $workloadMap[$person['user-id']] = &$person;
}

// 2. Update workload from tasks
foreach ($data['planning'] as $project) {
    foreach ($project['tasks'] as $task) {
        $assigneeId = $task['assigned_to'];
        $hours = floatval($task['hours']);

        if (isset($workloadMap[$assigneeId])) {
            $workloadMap[$assigneeId]['allocated'] += $hours;
            $workloadMap[$assigneeId]['left'] -= $hours;
            $workloadMap[$assigneeId]['Task'] += 1;
        }
    }
}

// 3. Prepare final JSON and date
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

// 4. Insert or update row (without client_priority)
$stmtCheck = $conn->prepare("SELECT id FROM daily_planning_data WHERE plan_date = ?");
$stmtCheck->bind_param("s", $date);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result && $result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE daily_planning_data SET data = ? WHERE plan_date = ?");
    $stmt->bind_param("ss", $finalJson, $date);
} else {
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
