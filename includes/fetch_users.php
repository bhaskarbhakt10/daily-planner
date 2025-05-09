<?php
require '../config/db.php';
$users = [];

$result = $conn->query("SELECT firstname FROM users");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['firstname'];
}
echo json_encode($users);
?>
