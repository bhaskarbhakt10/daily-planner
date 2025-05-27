<?php require_once 'templates/header.php'; ?>
<?php
$projects = [];
$users = [];
$hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];

// $selectedDate = $_GET['edit_date'] ?? $_GET['selected_date'] ?? date('Y-m-d');

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

$editDate = $_GET['edit_date'] ?? null;
$selectedDate = $editDate ?? ($_GET['selected_date'] ?? date('Y-m-d'));
$editMode = isset($editDate);

// $editMode = false;
// $editDate = $_GET['edit_date'] ?? null;

if ($editMode) {
    $editDate = $selectedDate;

    // Fetch the existing record for that date
    $stmt = $conn->prepare("SELECT data FROM daily_planning_data WHERE plan_date = ?");
    $stmt->bind_param("s", $editDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $existingData = [];
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existingData = json_decode($row['data'], true);
    }
}


?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
<script>
    $(function () {
    $("#taskTable").sortable({
        items: ".project-group",
        handle: ".project-drag-handle",
        axis: "y",
        containment: "parent",
        tolerance: "pointer",
        placeholder: "sortable-placeholder",
        update: function () {
        console.log("Projects reordered (create mode)");
        }
    });
});

</script>

<!-- <style>
.project-drag-handle {
  cursor: move;
  margin-right: 6px;
  color: #888;
  font-size: 18px;
  display: inline-block;
}

.sortable-placeholder {
  background-color: #f0f0f0;
  border: 2px dashed #ccc;
  height: 50px;
}
</style> -->


    <form method="GET" style="margin: 20px;">
        <label for="selected_date">Select Date:</label>
        <input 
            type="text" 
            id="selected_date" 
            name="selected_date" 
            value="<?= htmlspecialchars($selectedDate) ?>" 
            readonly
        >

    </form>
    <input type="hidden" id="hidden_date" value="<?= htmlspecialchars($selectedDate) ?>">

    <script>
    $(function () {
        const selectedDate = $('#hidden_date').val();
        $("#selected_date").datepicker({
            dateFormat: "yy-mm-dd"
        }).datepicker("setDate", selectedDate);
    });
</script>



<!-- <form method="GET" style="margin: 20px;">
    <label for="selected_date">Select Date:</label>
    <input 
    type="text" 
    id="selected_date" 
    name="selected_date" 
    value=" 
>

</form>
<input type="hidden" id="hidden_date" value=""> -->




<div class="layout-wrapper">
    <div class="left-panel">
        <?php include 'templates/planner_form.php'; ?>
    </div>
    
    <div class="right-panel">
        <?php include 'templates/workload_table.php'; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

