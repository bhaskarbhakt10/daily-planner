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

function submitData() {
    const planning = [];
    const workload = [];

    // Collect planning data
    document.querySelectorAll('.project-group').forEach(group => {
        const rows = group.querySelectorAll('tr');
        const projectSelect = group.querySelector('select[name="project[]"]');
        const projectName = projectSelect ? projectSelect.value : null;
        const tasks = [];

        rows.forEach(row => {
            const taskInput = row.querySelector('input[name="task_description[]"]');
            const hoursSelect = row.querySelector('select[name="hours[]"]');
            const assignedSelect = row.querySelector('select[name="assigned_to[][]"]');

            if (taskInput && hoursSelect && assignedSelect && taskInput.value.trim() !== "") {
                tasks.push({
                    task_description: taskInput.value.trim(),
                    assigned_to: assignedSelect.value,
                    hours: parseFloat(hoursSelect.value)
                });
            }
        });

        if (projectName && tasks.length > 0) {
            planning.push({
                project_name: projectName,
                tasks: tasks
            });
        }
    });

    // Collect workload data
    document.querySelectorAll('.workload-table tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 4) {
            workload.push({
                firstname: cells[0].innerText.trim(),
                allocated: parseFloat(cells[1].innerText.trim()),
                left: parseFloat(cells[2].innerText.trim()),
                Task: parseInt(cells[3].innerText.trim())
            });
        }
    });

    const jsonData = {
        planning: planning,
        workload: workload
    };

    

    // Send via AJAX (Fetch API)
    fetch('includes/submit_planning.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => response.text())
    .then(data => {
        alert('Data submitted successfully!');
        console.log('Server response:', data);
    
        // Update the workload table
        const rows = document.querySelectorAll('.workload-table tbody tr');
    
        rows.forEach(row => {
            const nameCell = row.querySelector('td');
            const name = nameCell.innerText.trim().toUpperCase();
    
            const matchingUser = workload.find(user => user.firstname.toUpperCase() === name);
            if (matchingUser) {
                row.cells[1].innerText = matchingUser.allocated; // Allocated
                row.cells[2].innerText = matchingUser.left;      // Left
                row.cells[3].innerText = matchingUser.Task;      // Tasks
    
                // Re-apply colors just in case
                row.cells[1].style.background = 'lightgreen';
                row.cells[2].style.background = 'lightblue';
                row.cells[3].style.background = 'orange';
            }
        });
    })
    
    
    .catch(error => {
        console.error('Error submitting data:', error);
        alert('Submission failed. See console for details.');
    });
}
