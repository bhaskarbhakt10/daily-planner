<?php
require 'config/db.php';
$projects = [];
$users = [];
$hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];

$result = $conn->query("SELECT Project_Name FROM projects");
while ($row = $result->fetch_assoc()) {
    $projects[] = $row['Project_Name'];
}

$result = $conn->query("SELECT firstname FROM users Where is_active = '1' AND id NOT IN (1, 27, 38)");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['firstname'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Required for Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="assets/js/planner.js"></script>
</body>

</html>
