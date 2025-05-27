function addMainRow() {
  const table = document.querySelector("#taskTable");
  const tbody = document.createElement("tbody");
  tbody.classList.add("project-group");

  const row = document.createElement("tr");
  row.innerHTML = `
        <td rowspan="1" class="project-cell">
            <span class="project-drag-handle">&#9776;</span>
            <select name="project[]" class="searchable-dropdown">
                ${projects
                  .map((p) => `<option value="${p.id}">${p.name}</option>`)
                  .join("")}
            </select>
        </td>
        <td rowspan="1">
            <input type="checkbox" name="client_priority[]" value="1" />
        </td>
        <td><input type="text" name="task_description[]" /></td>
        <td>
            <select name="hours[]">
                ${hours
                  .map((h) => `<option value="${h}">${h}</option>`)
                  .join("")}
            </select>
        </td>
        <td>
            <select name="assigned_to[][]" class="searchable-dropdown">
                ${users
                  .map((u) => `<option value="${u.id}">${u.name}</option>`)
                  .join("")}
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

  // Wait for the DOM to update and reinitialize dropdown plugins
  setTimeout(refreshSelect2, 50);
}


function addSubRow(button) {
  const row = button.closest("tr");
  const tbody = row.closest("tbody");

  const projectCell = tbody.querySelector(".project-cell");
  const currentRowspan = parseInt(projectCell.getAttribute("rowspan"));
  projectCell.setAttribute("rowspan", currentRowspan + 1);

  const priorityCell = tbody.querySelector('td[rowspan].priority-cell');
  if (priorityCell) {
    const priorityRowspan = parseInt(priorityCell.getAttribute("rowspan"));
    priorityCell.setAttribute("rowspan", priorityRowspan + 1);
  }

  const newRow = document.createElement("tr");
  newRow.innerHTML = `
        <!-- Placeholder for Priority column (empty cell) -->
        <td></td>

        <td><input type="text" name="task_description[]" /></td>

        <td>
            <select name="hours[]">
                ${hours.map(h => `<option value="${h}">${h}</option>`).join("")}
            </select>
        </td>

        <td>
            <select name="assigned_to[][]" class="searchable-dropdown">
                ${users.map(u => `<option value="${u.id}">${u.name}</option>`).join("")}
            </select>
            <button type="button" class="action-btn remove" onclick="removeSubRow(this)">Remove</button>
        </td>

        <td></td> <!-- Action column still renders in main row -->
    `;

  tbody.appendChild(newRow);
  setTimeout(refreshSelect2, 50);
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
  if (typeof $.fn.select2 !== "function") {
    console.warn("Select2 is not loaded yet.");
    return;
  }

  $(".searchable-dropdown").each(function () {
    const $select = $(this);

    // Destroy previous instance
    if ($select.hasClass("select2-hidden-accessible")) {
      $select.select2("destroy");
    }

    // Re-init
    $select.select2({
      width: "resolve",
      placeholder: "Select an option",
    });
  });
}

$(document).ready(function () {
  if (typeof $.fn.select2 !== "function") {
    console.error("Select2 not loaded!");
    return;
  }

  $(".searchable-dropdown").select2({
    width: "resolve",
  });
});

function removeSubRow(button) {
  const row = button.closest("tr");
  const tbody = row.closest("tbody");
  const allRows = Array.from(tbody.querySelectorAll("tr"));

  if (allRows.length === 1) {
    // Only 1 developer, remove whole project group
    tbody.remove();
    return;
  }

  if (row === allRows[0]) {
    // If it's the first developer row (main row), promote the second one properly
    const secondRow = allRows[1];

    // Copy actual values from inputs/selects
    const taskInput = secondRow.querySelector(
      'input[name="task_description[]"]'
    );
    const hoursSelect = secondRow.querySelector('select[name="hours[]"]');
    const assignedSelect = secondRow.querySelector(
      'select[name="assigned_to[][]"]'
    );

    const taskInputMain = row.querySelector('input[name="task_description[]"]');
    const hoursSelectMain = row.querySelector('select[name="hours[]"]');
    const assignedSelectMain = row.querySelector(
      'select[name="assigned_to[][]"]'
    );

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
  const updatedRows = Array.from(tbody.querySelectorAll("tr"));
  const projectCell = tbody.querySelector(".project-cell");
  if (projectCell) {
    projectCell.setAttribute("rowspan", updatedRows.length);
  }
}

function removeRow(button) {
  const row = button.closest("tr");
  const tbody = row.closest("tbody");
  tbody.remove();
}

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const hasDateParam = urlParams.has("selected_date");
  const dateInput = document.getElementById("selected_date");
  if (dateInput && !hasDateParam) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.value = today;
  }

  const submitBtn = document.getElementById("submit_btn");
  if (submitBtn) {
    submitBtn.addEventListener("click", submitData); // Hook up your submitData function
  }

  console.log("DOM fully loaded and ready!");
});

$(function () {
  $("#selected_date").datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: 0
  });

  const initialDate = $("#selected_date").val();
  if (initialDate) {
    $("#selected_date").datepicker("setDate", initialDate);
  }
});


function submitData() {
  const projectGroups = document.querySelectorAll("tbody.project-group");

  const rawPlanning = [];
  const workloadMap = {};

  const selectedDateInput = document.getElementById("selected_date");
  let selectedDate = selectedDateInput ? selectedDateInput.value : null;
  console.log("Selected Date:", selectedDate);

  // Convert MM/DD/YYYY → YYYY-MM-DD
  const parts = selectedDate.split("/");
  if (parts.length === 3) {
    const [month, day, year] = parts;
    selectedDate = `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  }
  if (!selectedDate) {
    alert("Please select a date before submitting.");
    return;
  }

  projectGroups.forEach((group) => {
    const rows = group.querySelectorAll("tr");

    const projectSelect = group.querySelector('select[name="project[]"]');
    const projectId = group.querySelector('select[name="project[]"]')?.value || null;


    if (!projectId) {
      console.warn("Project ID is null or empty in one of the groups");
      return;
    }

    const priorityCheckbox = group.querySelector('input[name="client_priority[]"]');
    const clientPriority = priorityCheckbox?.checked ? 1 : 0;

    const tasks = [];

    rows.forEach((row) => {
      const taskInput = row.querySelector('input[type="text"]');
      const hoursSelect = row.querySelector('select[name^="hours"]');
      const assignedSelects = row.querySelectorAll('select[name^="assigned_to"]');

      assignedSelects.forEach((assignedSelect) => {
        const assignedTo = parseInt($(assignedSelect).val(), 10);
        const taskHours = parseFloat(hoursSelect?.value || "0");

        if (
          taskInput?.value.trim() &&
          !isNaN(assignedTo) &&
          !isNaN(taskHours)
        ) {
          tasks.push({
            task_description: taskInput.value.trim(),
            assigned_to: assignedTo,
            hours: taskHours,
          });

          // Track workload
          if (!workloadMap[assignedTo]) {
            workloadMap[assignedTo] = { "user-id": assignedTo, allocated: 0, left: 8, Task: 0 };
          }

          workloadMap[assignedTo].allocated += taskHours;
          workloadMap[assignedTo].left = 8 - workloadMap[assignedTo].allocated;
          workloadMap[assignedTo].Task += 1;
        }
      });
    });

    if (tasks.length > 0) {
      rawPlanning.push({
        project_id: projectId,
        client_priority: clientPriority,
        tasks,
      });
    }
  });

  // Sort and add positions
  const planning = rawPlanning.sort((a, b) => b.client_priority - a.client_priority)
    .map((proj, idx) => ({ ...proj, position: idx + 1 }));

  const workload = Object.values(workloadMap);

  const jsonData = {
    date: selectedDate,
    planning,
    workload,
  };

  console.log("Final JSON being sent:", JSON.stringify(jsonData, null, 2));

  fetch("includes/submit_planning.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(jsonData),
  })
    .then((response) => response.text())
    .then((text) => {
      const data = JSON.parse(text);
      if (data.status === "success") {
        alert("Data submitted successfully!");
      } else {
        alert("Submission failed: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error submitting data:", error);
      alert("Submission failed. See console for details.");
    });
}


$(document).ready(function () {
  function updateWorkloadTable() {
    const workload = {};

    // Iterate each task row to calculate workload

    $(".project-group").each(function () {
      $(this)
        .find("tr")
        .each(function () {
          const $row = $(this);
          const hours =
            parseFloat($row.find('select[name="hours[]"]').val()) || 0;

          $row.find('select[name="assigned_to[][]"]').each(function () {
            let selectedUsers = $(this).val();
            if (!selectedUsers) return;

            // Force to array in case single select
            if (!Array.isArray(selectedUsers)) {
              selectedUsers = [selectedUsers];
            }

            selectedUsers.forEach((userId) => {
              const key = userId.toString().toUpperCase();

              if (!workload[key]) {
                workload[key] = { allocated: 0, tasks: 0 };
              }

              workload[key].allocated += hours;
              workload[key].tasks += 1;
            });
          });
        });
    });

    const updatedWorkload = [];

    // Update DOM table and collect data
    $(".workload-row").each(function () {
      const $row = $(this);
      const key = $row.data("name"); // This should now be user ID uppercased
      const userId = parseInt($row.data("user-id"));
      const data = workload[key];

      const allocated = data ? data.allocated : 0;
      const left = 8 - allocated;
      const tasks = data ? data.tasks : 0;

      $row.find("td").eq(1).text(allocated); // Allocated
      $row.find("td").eq(2).text(left); // Left
      $row.find("td").eq(3).text(tasks); // Tasks

      const rowData = {
        "user-id": userId,
        allocated,
        left,
        Task: tasks,
      };
      $row.data("workload-updated", rowData);
      updatedWorkload.push(rowData);
    });

    return updatedWorkload;
  }

  // Bind to form/input changes
  $(document).on(
    "input change",
    "#taskTable select, #taskTable input",
    updateWorkloadTable
  );

  // Bind to dynamic row updates
  $(document).on("click", ".add, .remove", function () {
    setTimeout(updateWorkloadTable, 100); // allow DOM change
  });

  // Initial table update
  updateWorkloadTable();

  // Expose it globally if needed elsewhere
  window.getUpdatedWorkload = updateWorkloadTable;
});

$(document).ready(function () {
    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const todayIndex = new Date().getDay(); // 0 = Sunday, 1 = Monday, ...
    const todayDay = days[todayIndex];

    // Highlight today's header column
    const $todayHeader = $(`th[data-day="${todayDay}"]`);
    if ($todayHeader.length) {
        $todayHeader.css({
            backgroundColor: '#003300',
            fontWeight: 'bold'
        });

        // Scroll to the column (if horizontal scroll is enabled)
        $('.container2').scrollLeft($todayHeader.position().left);
    }

    // Auto-fetch workload for current day
    fetch("includes/fetch_workload.php?day=" + todayDay)
        .then((res) => res.text())
        .then((html) => {
    const rightPanel = document.querySelector(".right-panel2");
    if (rightPanel) {
        rightPanel.innerHTML = html;
    } else {
        console.warn("Element .right-panel2 not found in DOM.");
    }
})

});

// Event binding for clicking on weekday headers
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".clickable-day").forEach(function (header) {
        header.addEventListener("click", function () {
            const day = this.getAttribute("data-day");

            fetch("includes/fetch_workload.php?day=" + day)
                .then((res) => res.text())
                .then((html) => {
                    document.querySelector(".right-panel2").innerHTML = html;
                })
                .catch((err) => console.error("Error loading workload:", err));
        });
    });
});

$(function () {
    let groupedRows = [];

    $("#sortable").sortable({
        items: ".sortable-project",
        handle: ".project-drag-handle",
        helper: function (e, tr) {
            const projectId = tr.data("project-id");
            groupedRows = $(`tr[data-project-id='${projectId}']`);

            const helper = $("<table/>").append(groupedRows.clone());
            helper.css("background", "#f0f0f0");
            return helper;
        },
        start: function (e, ui) {
            const rowCount = groupedRows.length;
            ui.placeholder.height(rowCount * ui.item.height());
        },
        stop: function (e, ui) {
            const projectId = ui.item.data("project-id");
            const newIndex = $(".sortable-project").index(ui.item);
            const tbody = $("#sortable");

            groupedRows.detach();

            if (newIndex === 0) {
                tbody.prepend(groupedRows);
            } else {
                const target = $(".sortable-project").eq(newIndex - 1);
                const lastRow = $(`tr[data-project-id='${target.data("project-id")}']`).last();
                groupedRows.insertAfter(lastRow);
            }
            // Save order to DB
            let order = [];
            $(".sortable-project").each(function (i) {
                const pid = $(this).data("project-id");
                order.push({ project_id: pid, position: i + 1 });
            });

            $.post("includes/update_project_order.php", { order: JSON.stringify(order) }, function (res) {
                console.log("Order updated");
            });
        }
    }).disableSelection();
});