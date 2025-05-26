<?php 
require_once 'templates/header.php';
require_once 'config/db.php';

$currentDateObj = new DateTime();
$startOfWeek = clone $currentDateObj->modify('Monday this week');
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');
$startDate = $startOfWeek->format('Y-m-d');
$endDate = $endOfWeek->format('Y-m-d');

$query = "SELECT * FROM daily_planning_data WHERE plan_date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($query);

$weekData = ['monday'=>[], 'tuesday'=>[], 'wednesday'=>[], 'thursday'=>[], 'friday'=>[], 'saturday'=>[]];
$projectsSet = [];

while ($row = $result->fetch_assoc()) {
    $day = strtolower(date('l', strtotime($row['plan_date'])));
    $entries = json_decode($row['data'], true)['planning'];
    foreach ($entries as $entry) {
        $project = $entry['project_id'];
        $projectsSet[$project] = [
            'position' => $entry['position'],
            'priority' => $entry['client_priority'] ?? 0
        ];
        foreach ($entry['tasks'] as $task) {
            $weekData[$day][$project][] = [
                'task' => $task['task_description'],
                'hours' => $task['hours'],
                'user' => $task['assigned_to']
            ];
        }
    }
}

uasort($projectsSet, fn($a, $b) => $a['position'] <=> $b['position']);
$projects = array_keys($projectsSet);
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

$projectNameMap = [];
if (!empty($projects)) {
    $projectIds = implode(',', array_map('intval', $projects));
    $projectQuery = "SELECT project_id, project_name FROM projects WHERE project_id IN ($projectIds)";
    $projectResult = $conn->query($projectQuery);
    while ($row = $projectResult->fetch_assoc()) {
        $projectNameMap[$row['project_id']] = $row['project_name'];
    }
}

$users = [];
$userNameMap = [];
$result = $conn->query("SELECT id, firstname FROM users WHERE is_active = '1' AND id NOT IN (1, 27, 38)");
while ($row = $result->fetch_assoc()) {
    $users[] = ['id' => $row['id'], 'name' => $row['firstname']];
    $userNameMap[$row['id']] = $row['firstname'];
}
?>

<!-- Include jQuery and jQuery UI -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>

<style>
.project-drag-handle {
    cursor: move;
    display: inline-block;
    margin-right: 5px;
    color: #999;
}
.edit-button {
    font-size: 14px;
    margin-left: 6px;
    color: #fff;
    text-decoration: none;
}
</style>

<div class="layout-wrapper2">
    <div class="left-panel2">
        <div class="container2">
            <table class="week-planner">
                <thead>
                    <tr>
                        <th class="sticky-col">PROJECTS</th>
                        <?php
                        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        foreach ($weekdays as $index => $day):
                            $dateForThisDay = (new DateTime('Monday this week'))->modify("+$index days")->format('Y-m-d');
                        ?>
                            <th class="clickable-day" colspan="3" data-day="<?= strtolower($day) ?>">
                                <?= $day ?>
                                <a href="index.php?edit_date=<?= $dateForThisDay ?>" class="edit-button" title="Edit Plan">✏️</a>
                            </th>
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

                <tbody id="sortable">
                    <?php foreach ($projects as $project): ?>
                        <?php
                        $maxRows = 0;
                        foreach ($days as $day) {
                            $count = isset($weekData[$day][$project]) ? count($weekData[$day][$project]) : 0;
                            if ($count > $maxRows) $maxRows = $count;
                        }
                        ?>
                        <?php for ($i = 0; $i < $maxRows; $i++): ?>
                            <tr class="<?= ($i === 0) ? 'sortable-project' : '' ?>" data-project-id="<?= $project ?>">
                                <?php if ($i == 0): ?>
                                    <td class="sticky-col" rowspan="<?= $maxRows ?>">
                                        <span class="project-drag-handle">&#9776;</span>
                                        <?php
                                        $projectName = $projectNameMap[$project] ?? "Project #$project";
                                        $isPriority = $projectsSet[$project]['priority'] ?? 0;
                                        ?>
                                        <span style="<?= $isPriority ? 'color: orange; font-weight: bold;' : '' ?>">
                                            <?= htmlspecialchars($projectName) ?>
                                        </span>
                                    </td>
                                <?php endif; ?>
                                <?php foreach ($days as $day): ?>
                                    <?php
                                    $task = $weekData[$day][$project][$i] ?? ['task' => '', 'hours' => '', 'user' => ''];
                                    $assignedName = $userNameMap[$task['user']] ?? '';
                                    ?>
                                    <td><input type="text" name="task_description[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($task['task']) ?>" /></td>
                                    <td><input type="text" name="hours[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($task['hours']) ?>" /></td>
                                    <td><input type="text" name="assigned_to[<?= $project ?>][<?= $day ?>][]" value="<?= htmlspecialchars($assignedName) ?>" /></td>
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
