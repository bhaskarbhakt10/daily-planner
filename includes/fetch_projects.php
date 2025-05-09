<?php
require '../config/db.php';
$projects = [];

$result = $conn->query("SELECT Project_Name FROM project");
while ($row = $result->fetch_assoc()) {
    $projects[] = $row['Project_Name'];
}
echo json_encode($projects);
?>
