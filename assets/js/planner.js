function addMainRow() {
    const table = document.querySelector("#taskTable");
    const tbody = document.createElement("tbody");
    tbody.classList.add("project-group");

    const row = document.createElement("tr");
    row.innerHTML = `
        <td rowspan="1" class="project-cell">
            <select name="project[]" class="searchable-dropdown">
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
            <select name="assigned_to[][]" class="searchable-dropdown">
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
    refreshSelect2();

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
            <select name="assigned_to[][]" class="searchable-dropdown">
                ${users.map(u => `<option value="${u}">${u}</option>`).join('')}
            </select>
            <button type="button" class="action-btn remove" style="margin-top: 4px;" onclick="removeSubRow(this)">Remove</button>
        </td>
        <td></td>
    `;

    tbody.appendChild(newRow);
    refreshSelect2();


}

// function refreshSelect2() {
//     $('.searchable-dropdown').each(function () {
//         // Remove previous instance if any
//         if ($(this).hasClass("select2-hidden-accessible")) {
//             $(this).select2('destroy');
//         }

//         // Initialize select2 again
//         $(this).select2({
//             width: 'style', // Or use 'resolve' if you want it to auto-size
//             placeholder: 'Select an option'
//         });
//     });
// }

// function refreshSelect2() {
//     $('.searchable-dropdown').each(function () {
//         if (!$(this).hasClass("select2-hidden-accessible")) {
//             $(this).select2({
//                 width: 'resolve',
//                 placeholder: 'Select an option'
//             });
//         }
//     });
// }

function refreshSelect2() {
    // Defer to ensure Select2 is loaded
    if (typeof $.fn.select2 !== 'function') {
        console.warn('Select2 is not loaded yet.');
        return;
    }

    $('.searchable-dropdown').each(function () {
        const $select = $(this);

        // Destroy previous instance
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2('destroy');
        }

        // Re-init
        $select.select2({
            width: 'resolve',
            placeholder: 'Select an option'
        });
    });
}


$(document).ready(function () {
    if (typeof $.fn.select2 !== 'function') {
        console.error('Select2 not loaded!');
        return;
    }

    $('.searchable-dropdown').select2({
        width: 'resolve'
    });
});




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

document.addEventListener('DOMContentLoaded', function () {
    
    const urlParams = new URLSearchParams(window.location.search);
    const hasDateParam = urlParams.has('selected_date');
    const dateInput = document.getElementById('selected_date');
    if (dateInput && !hasDateParam) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }

    
    const submitBtn = document.getElementById('submit_btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitData); // Hook up your submitData function
    }

    
    console.log('DOM fully loaded and ready!');
});

// $(function () {
//   //Datepicker
//   const today = new Date().toISOString().split('T')[0];
//   $("#selected_date").datepicker({
//       dateFormat: "yy-mm-dd",
//       defaultDate: today,
//       onSelect: function (dateText) {
//           $("#hidden_date").val(dateText);
          
//       }
//   }).datepicker("setDate", today);
// });

function submitData() {
    const planning = [];
    const workload = [];

    const selectedDateInput = document.getElementById('hidden_date');
    const selectedDate = selectedDateInput ? selectedDateInput.value : null;

    if (!selectedDate) {
        alert('Please select a date before submitting.');
        return;
    }

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
        date: selectedDate,
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
    .then(response => response.json())
    .then(data => {
    if (data.status === 'success') {
        alert('Data submitted successfully!');

        const updatedWorkload = data.updatedWorkload;

        const rows = document.querySelectorAll('.workload-table tbody tr');

        rows.forEach(row => {
            const nameCell = row.querySelector('td');
            const name = nameCell.innerText.trim().toUpperCase();

            const matchingUser = updatedWorkload.find(user => user.firstname.toUpperCase() === name);
            if (matchingUser) {
                row.cells[1].innerText = matchingUser.allocated;
                row.cells[2].innerText = matchingUser.left;
                row.cells[3].innerText = matchingUser.Task;
            } else {
                row.cells[1].innerText = '0';
                row.cells[2].innerText = '8';
                row.cells[3].innerText = '0';
            }

            // Apply colors
            row.cells[1].style.background = 'lightgreen';
            row.cells[2].style.background = 'lightblue';
            row.cells[3].style.background = 'orange';
        });
    } else {
        alert('Submission failed: ' + data.message);
    }
    })   
        .catch(error => {
            console.error('Error submitting data:', error);
            alert('Submission failed. See console for details.');
        });
}


$(document).ready(function () {
    function updateWorkloadTable() {
        // Reset the workload table to zero
        const workload = {};

        // Loop through each planner task row
        $('#taskTable tbody tr').each(function () {
            const $row = $(this);
            const hours = parseFloat($row.find('select[name="hours[]"]').val()) || 0;

            // Handle all assigned developers in this row
            $row.find('select[name="assigned_to[][]"]').each(function () {
                const user = $(this).val();
                if (!user) return;

                const username = user.toUpperCase();

                if (!workload[username]) {
                    workload[username] = { allocated: 0, tasks: 0 };
                }

                workload[username].allocated += hours;
                workload[username].tasks += 1;
            });
        });

        // Update the workload table
        $('.workload-row').each(function () {
            const $row = $(this);
            const name = $row.data('name'); // already uppercase
            const data = workload[name];

            const allocated = data ? data.allocated : 0;
            const left = 8 - allocated;
            const tasks = data ? data.tasks : 0;

            $row.find('td').eq(1).text(allocated); // Allocated
            $row.find('td').eq(2).text(left);      // Left
            $row.find('td').eq(3).text(tasks);     // Tasks
        });
    }

    // Bind change events to update workload live
    $(document).on('input change', '#taskTable select, #taskTable input', updateWorkloadTable);

    // Also trigger after adding/removing rows
    $(document).on('click', '.add, .remove', function () {
        setTimeout(updateWorkloadTable, 100); // slight delay for DOM changes
    });

    // Initial calculation
    updateWorkloadTable();
});


document.querySelectorAll('.clickable-day').forEach(function(header) {
    console.log('Attaching click to:', header); // <== add this
    header.addEventListener('click', function() {
        const day = this.getAttribute('data-day');
        console.log('Clicked day:', day); // <== add this

        fetch('includes/fetch_workload.php?day=' + day)
            .then(res => res.text())
            .then(html => {
                document.querySelector('.right-panel2').innerHTML = html;
            })
            .catch(err => console.error('Error loading workload:', err));
    });
});
