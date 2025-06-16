<?php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_id = intval($_POST['board_id']);
    $task_name = trim($_POST['task_name']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?? null;

    if (!empty($task_name)) {
        $assigned_user = $_POST['assigned_user'] ?? null;
        $stmt = $conn->prepare("INSERT INTO tasks (board_id, task_name, status, priority, due_date, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $board_id, $task_name, $status, $priority, $due_date, $assigned_user);
        $stmt->execute();
    }

    header("Location: /stratify/board_view.php?board_id=$board_id");
    exit();
}
?>
