<?php
require 'config/db.php';
$projects = [];
$users = [];
$hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];

$result = $conn->query("SELECT Project_Name FROM projects");
while ($row = $result->fetch_assoc()) {
    $projects[] = $row['Project_Name'];
}

$result = $conn->query("SELECT firstname FROM users");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['firstname'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout-wrapper">
  <div class="left-panel">
        <?php include 'templates/planner_form.php'; ?>
    </div>

    <div class="right-panel">
        <?php include 'templates/workload_table.php'; ?>
    </div>
</div>
<script>
    const projects = <?= json_encode($projects) ?>;
    const users = <?= json_encode($users) ?>;
    const hours = <?= json_encode($hours) ?>;
</script>
<script src="assets/js/planner.js"></script>
</body>

</html>
