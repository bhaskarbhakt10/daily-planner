<?php
require '../config/db.php';

file_put_contents('debug.log', "Raw input: " . file_get_contents("php://input"));

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

$data = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

echo json_encode([
  'status' => 'debug',
  'received' => $data,
  'raw' => file_get_contents('php://input')
]);
exit;


file_put_contents("log.txt", print_r($data, true), FILE_APPEND);

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
$planning = $data['planning'] ?? [];
$workload = $data['workload'] ?? [];
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

// 4. Extract priority from first project (optional, or adapt for multiple priorities)
$firstPriority = isset($data['planning'][0]['client_priority']) ? (int)$data['planning'][0]['client_priority'] : 0;

// 5. Insert or update row
$stmtCheck = $conn->prepare("SELECT id FROM daily_planning_data WHERE plan_date = ?");
$stmtCheck->bind_param("s", $date);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result && $result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE daily_planning_data SET data = ?, client_priority = ? WHERE plan_date = ?");
    $stmt->bind_param("sis", $finalJson, $firstPriority, $date);
} else {
    $stmt = $conn->prepare("INSERT INTO daily_planning_data (data, plan_date, client_priority) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $finalJson, $date, $firstPriority);
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
<?php
require '../config/db.php';

file_put_contents('debug.log', "Raw input: " . file_get_contents("php://input"));

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

$data = json_decode(file_get_contents("php://input"), true);

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

// 4. Extract priority from first project (optional, or adapt for multiple priorities)
$firstPriority = isset($data['planning'][0]['client_priority']) ? (int)$data['planning'][0]['client_priority'] : 0;

// 5. Insert or update row
$stmtCheck = $conn->prepare("SELECT id FROM daily_planning_data WHERE plan_date = ?");
$stmtCheck->bind_param("s", $date);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result && $result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE daily_planning_data SET data = ?, client_priority = ? WHERE plan_date = ?");
    $stmt->bind_param("sis", $finalJson, $firstPriority, $date);
} else {
    $stmt = $conn->prepare("INSERT INTO daily_planning_data (data, plan_date, client_priority) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $finalJson, $date, $firstPriority);
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
