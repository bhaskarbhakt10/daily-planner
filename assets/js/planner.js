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

  // Wait for the DOM to update and reinitialize Select2 or other dropdown logic
  setTimeout(refreshSelect2, 50);
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
        <td></td>
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
  //Datepicker
  //   const today = new Date().toISOString().split('T')[0];
  //   $("#selected_date").datepicker({
  //       dateFormat: "yy-mm-dd",
  //       defaultDate: today,
  //       onSelect: function (dateText) {
  //           $("#hidden_date").val(dateText);

  //       }
  //   }).datepicker("setDate", today);
  $("#selected_date").datepicker();
});

function submitData() {
  const planning = [];
  const workload = [];

  const selectedDateInput = document.getElementById("selected_date");
  let selectedDate = selectedDateInput ? selectedDateInput.value : null;
  console.log("Selected Date:", selectedDate);

  // Convert MM/DD/YYYY → YYYY-MM-DD if needed
  const parts = selectedDate.split("/");
  if (parts.length === 3) {
    const [month, day, year] = parts;
    selectedDate = `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  }
  if (!selectedDate) {
    alert("Please select a date before submitting.");
    return;
  }

  // Loop over each project group (each tbody)
  document.querySelectorAll(".project-group").forEach((group, groupIndex) => {
    // console.log(`Project group ${groupIndex + 1} found`);

    const rows = group.querySelectorAll("tr");
    // console.log(`Rows in project group ${groupIndex + 1}:`, rows.length);

    const projectSelect = group.querySelector('select[name="project[]"]');
    const projectId = projectSelect ? projectSelect.value : null; // DO NOT use select2 span
    if (!projectId) return;

    const tasks = [];

    rows.forEach((row) => {
      const taskInput = row.querySelector('input[type="text"]');
      const hoursSelect = row.querySelector('select[name^="hours"]');
      const assignedSelect = row.querySelector('select[name^="assigned_to"]');
      const assignedTo = assignedSelect ? $(assignedSelect).val() : null;

//       console.log("🔎 Row:", {
//   taskInput: taskInput?.value,
//   hours: hoursSelect?.value,
//   assignedTo: assignedSelect ? $(assignedSelect).val() : null
// });

      if (
        taskInput &&
        taskInput.value.trim() !== "" &&
        hoursSelect &&
        assignedTo
      ) {
        tasks.push({
          task_description: taskInput.value.trim(),
          assigned_to: parseInt(assignedTo),
          hours: parseFloat(hoursSelect.value),
        });
      }
    });

    if (tasks.length > 0) {
      planning.push({
        project_id: projectId,
        position: groupIndex + 1,
        tasks: tasks,
      });
    }


  });

  // ✅ Build workload array from .workload-table (make sure your table includes this!)
  document.querySelectorAll(".workload-table tbody tr").forEach((row) => {
    const userIdAttr = row.getAttribute("data-user-id");
    const cells = row.querySelectorAll("td");
    if (userIdAttr && cells.length >= 4) {
      workload.push({
        "user-id": parseInt(userIdAttr),
        allocated: parseFloat(cells[1].innerText.trim()),
        left: parseFloat(cells[2].innerText.trim()),
        Task: parseInt(cells[3].innerText.trim()),
      });
    }
  });

  const jsonData = {
    date: selectedDate,
    planning: planning,
    workload: workload,
  };

  console.log(
    "📦 Final JSON data being sent:",
    JSON.stringify(jsonData, null, 2)
  );

  fetch("includes/submit_planning.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(jsonData),
  })
    .then((response) => response.text())
    .then((text) => {
      //   console.log("Raw server response:", text); // 👈 Add this
      const data = JSON.parse(text); // This will throw if the response is invalid
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

document.addEventListener("DOMContentLoaded", function () {
  console.log("Script loaded");

  document.querySelectorAll(".clickable-day").forEach(function (header) {
    console.log("Attaching click to:", header);
    header.addEventListener("click", function () {
      const day = this.getAttribute("data-day");
      console.log("Clicked day:", day);

      fetch("includes/fetch_workload.php?day=" + day)
        .then((res) => res.text())
        .then((html) => {
          document.querySelector(".right-panel2").innerHTML = html;
        })
        .catch((err) => console.error("Error loading workload:", err));
    });
  });
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
    }

    // Scroll to the column (if horizontal scroll is enabled)
    $('.container2').scrollLeft($todayHeader.position().left);
});

$(function () {
    $(".week-planner tbody").sortable({
        items: "tr.project-separator", // Only move the first row of each group
        handle: ".project-drag-handle",
        helper: function (e, row) {
            row.children().each(function () {
                $(this).width($(this).width());
            });
            return row.clone();
        },
        update: function () {
            let order = [];
            $(".project-separator").each(function (i) {
                const $tbody = $(this).closest('tbody.sortable-project');
                const projectId = $tbody.data("project-id");
                order.push({
                    project_id: projectId,
                    position: i + 1
                });
            });

            $.post("includes/update_project_order.php", { order: JSON.stringify(order) }, function (res) {
                console.log("Order updated");
            });
        }
    });
});
