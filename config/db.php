<?php
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * Creates the `daily_planning_data` table if it doesn't exist.
 */
function createDailyPlanningTable(mysqli $conn): bool
{
    $sql = "
        CREATE TABLE IF NOT EXISTS daily_planning_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            plan_date DATE DEFAULT NULL
        )
    ";

    return $conn->query($sql) === TRUE;
}

createDailyPlanningTable($conn);
