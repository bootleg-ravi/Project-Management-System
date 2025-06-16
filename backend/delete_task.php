<?php
session_start();
require_once "../config.php";

if (isset($_GET['id']) && isset($_GET['board_id'])) {
    $task_id = intval($_GET['id']);
    $board_id = intval($_GET['board_id']);

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();

    header("Location: /stratify/board_view.php?board_id=$board_id");
    exit();
}
?>
