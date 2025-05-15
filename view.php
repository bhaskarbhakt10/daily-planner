<?php
require_once 'templates/header.php';
require_once 'config/db.php';

// Get current week range
$currentDateObj = new DateTime();
$startOfWeek = clone $currentDateObj->modify('Monday this week');
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');

$startDate = $startOfWeek->format('Y-m-d');
$endDate = $endOfWeek->format('Y-m-d');
$currentDay = strtolower(date('l'));

// Fetch planning data
$query = "SELECT * FROM daily_planning_data WHERE plan_date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($query);

// Process data
$weekData = ['monday'=>[], 'tuesday'=>[], 'wednesday'=>[], 'thursday'=>[], 'friday'=>[], 'saturday'=>[]];
$projectsSet = [];

while ($row = $result->fetch_assoc()) {
    $day = strtolower(date('l', strtotime($row['plan_date'])));
    $entries = json_decode($row['data'], true)['planning'];

    foreach ($entries as $entry) {
        $project = $entry['project_name'];
        $projectsSet[$project] = true;
        foreach ($entry['tasks'] as $task) {
            $weekData[$day][$project][] = [
                'task' => $task['task_description'],
                'hours' => $task['hours'],
                'user' => $task['assigned_to']
            ];
        }
    }
}

$projects = array_keys($projectsSet);
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

$result = $conn->query("SELECT firstname FROM users Where is_active = '1' AND id NOT IN (1, 27, 38)");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['firstname'];
}

?>

<div class="layout-wrapper2">
    <div class="left-panel2">
        <div class="container2">
          <table class="week-planner">
            <thead>
              <tr>
                <th class="sticky-col">PROJECTS</th>
                <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day): ?>
                  <th colspan="3" class="clickable-day" data-day="<?= strtolower($day) ?>"><?= $day ?></th>
                <?php endforeach; ?>

              </tr>
              <tr>
                <th class="sticky-col"></th>
                <?php for ($i = 0; $i < 6; $i++): ?>
                  <th>Task</th>
                  <th>Hours</th>
                  <th>Assigned To</th>
                <?php endfor; ?>
              </tr>
            </thead>
            <tbody>
<?php foreach ($projects as $project): ?>
    <?php
    // Find max rows needed for this project
    $maxRows = 0;
    foreach ($days as $day) {
        $count = isset($weekData[$day][$project]) ? count($weekData[$day][$project]) : 0;
        if ($count > $maxRows) {
            $maxRows = $count;
        }
    }

    for ($i = 0; $i < $maxRows; $i++): ?>
    <tr class="<?= ($i + 1 === $maxRows) ? 'project-separator' : '' ?>">

        <?php if ($i == 0): ?>
            <td class="sticky-col" rowspan="<?= $maxRows ?>"><?= htmlspecialchars($project) ?></td>
        <?php endif; ?>

        <?php foreach ($days as $day): ?>
            <?php
            $task = $weekData[$day][$project][$i] ?? ['task' => '', 'hours' => '', 'user' => ''];
            ?>
            <td>
                <input type="text" name="task_description[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($task['task']) ?>" />
            </td>
            <td>
                <input type="text" name="hours[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($task['hours']) ?>" />
            </td>
            <td>
                <input type="text" name="assigned_to[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($task['user']) ?>" />
            </td>
        <?php endforeach; ?>
    </tr>
    <?php endfor; ?>
<?php endforeach; ?>
</tbody>



          </table>
        </div>
    </div>

    <div class="right-panel2">
        <?php include 'templates/workload_table.php'; ?>
    </div>
</div>



<?php require_once 'templates/footer.php'; ?>
