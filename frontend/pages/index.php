<?php

// $projects = ["Website Redesign", "Mobile App", "API Development"];
// $users = ["Alice (Developer)", "Bob (Designer)", "Charlie (Developer)", "Diana (Designer)"];
// $hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];

$host = 'localhost';
$db   = 'daily_planner';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$projects = [];
$users = [];

// Fetch projects
$result = $conn->query("SELECT Project_Name FROM project");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row['Project_Name'];
    }
}

// Fetch dev/designers
$result = $conn->query("SELECT firstname FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row['firstname'] ;
    }
}

// Hardcoded hours
$hours = ["0.5", "1", "1.5", "2", "2.5", "3", "3.5", "4", "5", "6", "7", "8"];
?>


<!DOCTYPE html>
<html>
<head>
    <title>Daily Planner</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 20px;
        display: flex;
        gap: 20px;
    }

    .container {
        flex: 3;
    }

    .workload-table {
        flex: 1;
        border: 1px solid #ccc;
        padding: 10px;
    }

    h2 {
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    thead th {
        background-color: rgb(16, 57, 129);
        color: white;
        padding: 12px;
        text-align: left;
        font-size: 14px;
    }

    tbody td {
        background-color: #fff;
        padding: 10px;
        border: 1px solid #ccc;
        vertical-align: top;
    }

    select, input[type="text"] {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .action-btn {
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
        margin: 2px 0;
    }

    .add { background-color: green; }
    .add:hover { background-color: darkgreen; }

    .remove { background-color: red; }
    .remove:hover { background-color: darkred; }

    button[type="button"]:not(.action-btn) {
        background-color: green;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        margin-top: 15px;
    }

    button[type="button"]:not(.action-btn):hover {
        background-color: darkgreen;
    }

    .indent { padding-left: 25px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Planning</h2>

    <form method="post" action="">
        <table id="taskTable">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Task Description</th>
                    <th>No. of Hours</th>
                    <th>Assigned To</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="project-group">
            <tr>
            <td rowspan="1" class="project-cell">
            <select name="project[]">
                <?php foreach ($projects as $project): ?>
                    <option value="<?= htmlspecialchars($project) ?>"><?= htmlspecialchars($project) ?></option>
                <?php endforeach; ?>
            </select>

            </td>
                <td><input type="text" name="task_description[]" /></td>
                <td>
                    <select name="hours[]">
                        <?php foreach ($hours as $hour): ?>
                            <option value="<?= $hour ?>"><?= $hour ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                <select name="assigned_to[][]">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= htmlspecialchars($user) ?>"><?= htmlspecialchars($user) ?></option>
                    <?php endforeach; ?>
                </select>

                    <button type="button" class="action-btn remove" style="margin-top: 4px;" onclick="removeSubRow(this)">Remove</button>
                </td>

                <td>
                    <button type="button" class="action-btn add" onclick="addSubRow(this)">+ Add Dev/Designer</button><br>
                    <button type="button" class="action-btn remove" onclick="removeRow(this)">Remove</button>
                </td>
            </tr>

            </tbody>
        </table>
        <br>
        <button type="button" onclick="addMainRow()">Add Project Row</button>
        <button type="button" id="submitBtn" onclick="submitData()" disabled>Submit</button>
    </form>
</div>

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
                <tr style="text-align: center;">
                    <td><?= htmlspecialchars(strtoupper($user)) ?></td>
                    <td style="background: lightgreen;">0</td> <!-- Placeholder Allocated -->
                    <td style="background: lightblue;">8</td> <!-- Placeholder Left -->
                    <td style="background: orange;">0</td> <!-- Placeholder Tasks -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const projects = <?= json_encode($projects) ?>;
const users = <?= json_encode($users) ?>;
const hours = <?= json_encode($hours) ?>;

function addMainRow() {
    const table = document.querySelector("#taskTable");
    const tbody = document.createElement("tbody");
    tbody.classList.add("project-group");

    const row = document.createElement("tr");
    row.innerHTML = `
        <td rowspan="1" class="project-cell">
            <select name="project[]">
                ${projects.map(p => `<option value="${p}">${p}</option>`).join('')}
            </select>
        </td>
        <td><input type="text" name="task_description[]" /></td>
        <td>
            <select name="hours[]">
                ${hours.map(h => `<option value="${h}">${h}</option>`).join('')}
            </select>
        </td>
        <td>
            <select name="assigned_to[][]">
                ${users.map(u => `<option value="${u}">${u}</option>`).join('')}
            </select>
            <button type="button" class="action-btn remove" style="margin-top: 4px;" onclick="removeSubRow(this)">Remove</button>
        </td>
        <td>
            <button type="button" class="action-btn add" onclick="addSubRow(this)">+ Add Dev/Designer</button><br>
            <button type="button" class="action-btn remove" onclick="removeRow(this)">Remove</button>
        </td>
    `;
    tbody.appendChild(row);
    table.appendChild(tbody);
}


function addSubRow(button) {
    const row = button.closest("tr");
    const tbody = row.closest("tbody");

    const projectCell = tbody.querySelector(".project-cell");
    const currentRowspan = parseInt(projectCell.getAttribute("rowspan"));
    projectCell.setAttribute("rowspan", currentRowspan + 1);

    const newRow = document.createElement("tr");
    newRow.innerHTML = `
        <td><input type="text" name="task_description[]" /></td>
        <td>
            <select name="hours[]">
                ${hours.map(h => `<option value="${h}">${h}</option>`).join('')}
            </select>
        </td>
        <td>
            <select name="assigned_to[][]">
                ${users.map(u => `<option value="${u}">${u}</option>`).join('')}
            </select>
            <button type="button" class="action-btn remove" style="margin-top: 4px;" onclick="removeSubRow(this)">Remove</button>
        </td>
        <td></td>
    `;

    tbody.appendChild(newRow);
}



function removeSubRow(button) {
    const row = button.closest('tr');
    const tbody = row.closest('tbody');
    const allRows = Array.from(tbody.querySelectorAll('tr'));

    if (allRows.length === 1) {
        // Only 1 developer, remove whole project group
        tbody.remove();
        return;
    }

    if (row === allRows[0]) {
        // If it's the first developer row (main row), promote the second one properly
        const secondRow = allRows[1];

        // Copy actual values from inputs/selects
        const taskInput = secondRow.querySelector('input[name="task_description[]"]');
        const hoursSelect = secondRow.querySelector('select[name="hours[]"]');
        const assignedSelect = secondRow.querySelector('select[name="assigned_to[][]"]');

        const taskInputMain = row.querySelector('input[name="task_description[]"]');
        const hoursSelectMain = row.querySelector('select[name="hours[]"]');
        const assignedSelectMain = row.querySelector('select[name="assigned_to[][]"]');

        if (taskInput && hoursSelect && assignedSelect) {
            taskInputMain.value = taskInput.value;
            hoursSelectMain.value = hoursSelect.value;
            assignedSelectMain.value = assignedSelect.value;
        }

        secondRow.remove();
    } else {
        // Any other row — just remove it
        row.remove();
    }

    // Update rowspan
    const updatedRows = Array.from(tbody.querySelectorAll('tr'));
    const projectCell = tbody.querySelector('.project-cell');
    if (projectCell) {
        projectCell.setAttribute('rowspan', updatedRows.length);
    }
}



function removeRow(button) {
    const row = button.closest("tr");
    const tbody = row.closest("tbody");
    tbody.remove();
}


</script>

</body>
</html>
