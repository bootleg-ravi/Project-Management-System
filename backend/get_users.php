<?php
session_start();
require_once "../config.php";

header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT id, full_name FROM users ORDER BY full_name ASC");
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();

echo json_encode($users);
