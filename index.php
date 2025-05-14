<?php require_once 'templates/header.php'; ?>
<?php
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

<div class="layout-wrapper">
    <div class="left-panel">
        <?php include 'templates/planner_form.php'; ?>
    </div>
    
    <div class="right-panel">
        <?php include 'templates/workload_table.php'; ?>
    </div>
</div>
<?php require_once 'templates/footer.php'; ?>

