<?php require_once 'templates/header.php'; ?>


<?php
/***
 * 
 * 
 * Load Data based on current Week;
 * 
 */



$currentDateObj = (new DateTime());

$startOfWeekObj = clone $currentDateObj;
$startOfWeekObj->modify('Monday this week');

$endOfWeekObj = clone $currentDateObj;
$endOfWeekObj->modify('Saturday this week');

$currentDate = $currentDateObj->format('Y-m-d');

$startDate = ($startOfWeekObj->format('Y-m-d'));
$endDate = ($endOfWeekObj->format('Y-m-d'));


$getDataWithinRangeQuery  = "SELECT * FROM daily_planning_data WHERE planning_for BETWEEN '$startDate' AND '$endDate'";

$result = $conn->query($getDataWithinRangeQuery,);
?>

<?php require_once 'templates/footer.php'; ?>