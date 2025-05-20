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
            <select name="project[]" class="searchable-dropdown">
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
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
                <select name="assigned_to[]" class="searchable-dropdown">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
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
        <button type="button" id="submitBtn" onclick="submitData()">Submit</button>
        
    </form>
