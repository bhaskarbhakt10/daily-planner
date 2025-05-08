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
        tbody.remove();
        return;
    }

    if (row === allRows[0]) {
        const secondRow = allRows[1];

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
        row.remove();
    }

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
