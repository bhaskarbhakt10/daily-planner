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
                <tr class="workload-row" data-name="<?= htmlspecialchars(strtoupper($user)) ?>" style="text-align: center;">

                    <td><?= htmlspecialchars(strtoupper($user)) ?></td>
                    <td style="background: lightgreen;">0</td> <!-- Placeholder Allocated -->
                    <td style="background: lightblue;">8</td> <!-- Placeholder Left -->
                    <td style="background: orange;">0</td> <!-- Placeholder Tasks -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
