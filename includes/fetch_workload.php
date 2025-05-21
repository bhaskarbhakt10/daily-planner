<?php
require_once '../config/db.php';

// Error reporting for debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get and validate day
$day = strtolower($_GET['day'] ?? '');
$validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
if (!in_array($day, $validDays)) die('Invalid day.');

$startOfWeek = new DateTime('monday this week');
$dayOffsets = ['monday'=>0, 'tuesday'=>1, 'wednesday'=>2, 'thursday'=>3, 'friday'=>4, 'saturday'=>5];
$targetDate = (clone $startOfWeek)->modify("+{$dayOffsets[$day]} days")->format('Y-m-d');

// Get users
$users = [];
$workload = [];

$userResult = $conn->query("SELECT id, firstname FROM users WHERE is_active = '1' AND id NOT IN (1,27,38)");
while ($row = $userResult->fetch_assoc()) {
    $users[$row['id']] = $row['firstname'];
    $workload[$row['id']] = ['allocated' => 0, 'tasks' => 0];

}

// Get planning data
$query = "SELECT * FROM daily_planning_data WHERE plan_date = '$targetDate'";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $entries = json_decode($row['data'], true)['planning'];
    foreach ($entries as $entry) {
        foreach ($entry['tasks'] as $task) {
           $userId = $task['assigned_to'];
            if (isset($workload[$userId])) {
                $workload[$userId]['allocated'] += floatval($task['hours']);
                $workload[$userId]['tasks'] += 1;
            }
        }
    }
}

// Now output HTML (same format expected in workload_table.php)
?>
<div class="workload-table">
    <h2>Workload - <?= ucfirst($day) ?></h2>
    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #27408B; color: white;">
                <th>Developer</th>
                <th>Allocated</th>
                <th>Left</th>
                <th>Tasks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $userId => $name): 
            $allocated = $workload[$userId]['allocated'] ?? 0;
            $tasks = $workload[$userId]['tasks'] ?? 0;
            ?>
            <tr class="workload-row" data-user-id="<?= $userId ?>" data-name="<?= strtoupper((string)$userId) ?>">
                <td><?= htmlspecialchars(strtoupper($name)) ?></td>
                <td style="background: lightgreen;"><?= $allocated ?></td>
                <td style="background: lightblue;"><?= 8 - $allocated ?></td>
                <td style="background: orange;"><?= $tasks ?></td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>
