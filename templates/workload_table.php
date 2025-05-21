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
    <tr class="workload-row"
        data-user-id="<?= $user['id'] ?>"
        data-name="<?= strtoupper((string) $user['id']) ?>">
        <td><?= htmlspecialchars(strtoupper($user['name'])) ?></td>
        <td style="background: lightgreen;">0</td>
        <td style="background: lightblue;">8</td>
        <td style="background: orange;">0</td>
    </tr>
<?php endforeach; ?>


        </tbody>
    </table>
</div>
