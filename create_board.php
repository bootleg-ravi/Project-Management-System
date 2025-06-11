<?php
session_start();
require_once "../config.php";

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO boards (name, workspace_id, user_id) VALUES (?, ?, ?)");
$stmt->bind_param("sii", $board_name, $workspace_id, $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_name = trim($_POST['board_name']);
    $workspace_id = intval($_POST['workspace_id']);

    if (!empty($board_name)) {
        $stmt = $conn->prepare("INSERT INTO boards (workspace_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $workspace_id, $board_name);
        $stmt->execute();
    }
    header("Location: /stratify/dashboard.php?workspace_id=$workspace_id");
    exit();
}
?>
