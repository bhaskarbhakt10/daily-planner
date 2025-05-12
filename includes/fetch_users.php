<?php
require '../config/db.php';
$users = [];

$result = $conn->query("SELECT firstname FROM users WHERE is_active = '1' AND id NOT IN (1, 27, 38)");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['firstname'];
}
echo json_encode($users);
?>
