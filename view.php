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
?>

<style>
.container {
    overflow-x: auto;
    max-width: 100%;
}

.week-planner {
    border-collapse: collapse;
    min-width: 1000px;
}

.week-planner th, .week-planner td {
    border: 1px solid #ccc;
    padding: 10px;
    min-width: 160px;
    vertical-align: top;
    background: #fff;
    font-size: 13px;
}

.week-planner th {
    background: rgb(16, 57, 129); /* Deep blue */
    color: #fff;
    text-align: center;
    font-weight: bold;
}

.sticky-col {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 1;
    min-width: 180px;
    max-width: 180px;
}

thead .sticky-col {
    background: rgb(16, 57, 129); /* Deep blue */
    color: #fff;
    z-index: 2;
}

input[type="text"] {
  width: 100%;
  padding: 6px;
  font-size: 13px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

</style>


<div class="container">
  <table class="week-planner">
    <thead>
      <tr>
        <th class="sticky-col">PROJECTS</th>
        <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day): ?>
          <th colspan="3"><?= $day ?></th>
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
        <tr>
          <td class="sticky-col"><?= htmlspecialchars($project) ?></td>
          <?php for ($i = 0; $i < 6; $i++): ?>
            <td>
              <input type="text" name="task_description[<?= $project ?>][]" />
            </td>
            <td>
              <input type="text" name="hours[<?= $project ?>][]" />
            </td>
            <td>
              <input type="text" name="assigned_to[<?= $project ?>][]" />
            </td>
          <?php endfor; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>



<script>
$(document).ready(function() {
  $('.searchable-dropdown').select2({ width: 'resolve' });
});
</script>


<?php require_once 'templates/footer.php'; ?>
