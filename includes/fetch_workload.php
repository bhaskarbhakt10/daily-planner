<?php
require_once '../config/db.php';

$day = strtolower($_GET['day'] ?? '');
$validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

if (!in_array($day, $validDays)) {
    die('Invalid day provided.');
}

$startOfWeek = new DateTime('monday this week');
$dayOffsets = [
    'monday' => 0,
    'tuesday' => 1,
    'wednesday' => 2,
    'thursday' => 3,
    'friday' => 4,
    'saturday' => 5,
];

$startDate = (clone $startOfWeek)->modify("+{$dayOffsets[$day]} days")->format('Y-m-d');


// Fetch planning data for that date
$query = "SELECT * FROM daily_planning_data WHERE plan_date = '$startDate'";
$result = $conn->query($query);

$workload = [];
$usersResult = $conn->query("SELECT firstname FROM users WHERE is_active = '1' AND id NOT IN (1, 27, 38)");
$users = [];

while ($row = $usersResult->fetch_assoc()) {
    $name = $row['firstname'];
    $users[] = $name;
    $workload[$name] = ['allocated' => 0, 'tasks' => 0];
}

while ($row = $result->fetch_assoc()) {
    $entries = json_decode($row['data'], true)['planning'];
    foreach ($entries as $entry) {
        foreach ($entry['tasks'] as $task) {
            $user = $task['assigned_to'];
            $workload[$user]['allocated'] += floatval($task['hours']);
            $workload[$user]['tasks'] += 1;
        }
    }
}
echo "<h1>DEBUG: You clicked $day</h1>";


// Include same HTML logic as workload_table.php
?>

<div class="workload-table">
    <h2>Workload</h2>
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
            <?php foreach ($users as $user): ?>
                <tr class="workload-row" data-name="<?= htmlspecialchars(strtoupper($user)) ?>" style="text-align: center;">

                    <td><?= htmlspecialchars(strtoupper($user)) ?></td>
                    <td style="background: lightgreen;"><?= $workload[$user]['allocated'] ?></td>
<td style="background: lightblue;"><?= 8 - $workload[$user]['allocated'] ?></td>
<td style="background: orange;"><?= $workload[$user]['tasks'] ?></td>
<!-- Placeholder Tasks -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

