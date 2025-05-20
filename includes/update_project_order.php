<?php
require_once '../config/db.php';

if (!isset($_POST['order'])) {
    http_response_code(400);
    echo "Missing order data";
    exit;
}

$order = json_decode($_POST['order'], true);

// Build a map of project_id => new_position
$positionMap = [];
foreach ($order as $item) {
    $positionMap[$item['project_id']] = (int)$item['position'];
}

// Get current week date range
$startOfWeek = new DateTime('Monday this week');
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');
$startDate = $startOfWeek->format('Y-m-d');
$endDate = $endOfWeek->format('Y-m-d');

// Fetch all rows for current week
$query = "SELECT id, data FROM daily_planning_data WHERE plan_date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $data = json_decode($row['data'], true);

    // Update project positions
    foreach ($data['planning'] as &$project) {
        if (isset($positionMap[$project['project_id']])) {
            $project['position'] = $positionMap[$project['project_id']];
        }
    }

    // Re-sort planning array by position
    usort($data['planning'], function ($a, $b) {
        return $a['position'] <=> $b['position'];
    });

    // Save updated data back to DB
    $updatedJson = $conn->real_escape_string(json_encode($data));
    $updateQuery = "UPDATE daily_planning_data SET data = '$updatedJson' WHERE id = $id";
    $conn->query($updateQuery);
}

echo "success";
