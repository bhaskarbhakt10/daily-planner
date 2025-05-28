<?php
$planningData = $existingData['planning'] ?? [];
?>

<form method="post" action="">
    <table id="taskTable">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Priority</th>
                <th>Task Description</th>
                <th>No. of Hours</th>
                <th>Assigned To</th>
                <th>Action</th>
            </tr>
        </thead>

        <?php if (!empty($planningData)): ?>
            <?php foreach ($planningData as $project): ?>
                <tbody class="project-group">
                    <?php foreach ($project['tasks'] as $i => $task): ?>
                        <tr>
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= count($project['tasks']) ?>" class="project-cell">
                                    <span class="project-drag-handle">&#9776;</span>
                                    <select name="project[]" class="searchable-dropdown">
                                        <?php foreach ($projects as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $project['project_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td rowspan="<?= count($project['tasks']) ?>">
                                    <input type="checkbox" name="client_priority[]" value="1" <?= !empty($project['client_priority']) ? 'checked' : '' ?> />
                                </td>
                            <?php endif; ?>

                            <td><input type="text" name="task_description[]" value="<?= htmlspecialchars($task['task_description']) ?>" /></td>

                            <td>
                                <select name="hours[]">
                                    <?php foreach ($hours as $hour): ?>
                                        <option value="<?= $hour ?>" <?= $hour == $task['hours'] ? 'selected' : '' ?>>
                                            <?= $hour ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <select name="assigned_to[][]" class="searchable-dropdown">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= $user['id'] == $task['assigned_to'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- <button type="button" class="action-btn remove" onclick="removeSubRow(this)">Remove</button> -->
                            </td>

                            <td>
                                <button type="button" class="action-btn add" onclick="addSubRow(this)">+ Add Dev/Designer</button><br>
                                <button type="button" class="action-btn remove" onclick="removeRow(this)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
        <?php else: ?>
            <tbody class="project-group">
                <tr>
                    <td rowspan="1" class="project-cell">
                        <span class="project-drag-handle">&#9776;</span>
                        <select name="project[]" class="searchable-dropdown">
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td rowspan="1">
                        <input type="checkbox" name="client_priority[]" value="1" />
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
                        <select name="assigned_to[][]" class="searchable-dropdown">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="action-btn remove col-dev" onclick="removeSubRow(this)">Remove</button>
                    </td>

                    <td>
                        <button type="button" class="action-btn add" onclick="addSubRow(this)">+ Add Dev/Designer</button><br>
                        <button type="button" class="action-btn remove" onclick="removeRow(this)">Remove</button>
                    </td>
                </tr>
            </tbody>
        <?php endif; ?>
    </table>

    <br>
    <button type="button" onclick="addMainRow()">Add Project</button>
    <button type="button" id="submitBtn" onclick="submitData()">Submit</button>
</form>
