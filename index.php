<?php require_once 'templates/header.php'; ?>
<?php
$projects = [];
$users = [];
$hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];

// $result = $conn->query("SELECT Project_Name FROM projects");
// while ($row = $result->fetch_assoc()) {
//     $projects[] = $row['Project_Name'];
// }

// $result = $conn->query("SELECT firstname FROM users Where is_active = '1' AND id NOT IN (1, 27, 38)");
// while ($row = $result->fetch_assoc()) {
//     $users[] = $row['firstname'];
// }

$result = $conn->query("SELECT Project_Id, Project_Name FROM projects");
while ($row = $result->fetch_assoc()) {
    $projects[] = [
        'id' => $row['Project_Id'],
        'name' => $row['Project_Name']
    ];
}

$result = $conn->query("SELECT id, firstname FROM users WHERE is_active = '1' AND id NOT IN (1, 27, 38)");
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'name' => $row['firstname']
    ];
}
?>


<form method="GET" style="margin: 20px;">
    <label for="selected_date">Select Date:</label>
    <input 
        type="date" 
        id="selected_date" 
        name="selected_date" 
        value="<?php echo isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d'); ?>" 
        onchange="this.form.submit()"
    >
</form>
<input type="hidden" id="hidden_date" value="<?php echo $_GET['selected_date'] ?? date('Y-m-d'); ?>">

<!-- <div style="margin: 20px;">
    <label for="selected_date">Select Date:</label>
    <input 
        type="text" 
        id="selected_date" 
        readonly 
        style="cursor: pointer; width: 150px; padding: 6px;"
    >
</div> -->



<div class="layout-wrapper">
    <div class="left-panel">
        <?php include 'templates/planner_form.php'; ?>
    </div>
    
    <div class="right-panel">
        <?php include 'templates/workload_table.php'; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

