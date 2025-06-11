<?php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['board_id'], $_POST['workspace_id'])) {
    $board_id = intval($_POST['board_id']);
    $workspace_id = intval($_POST['workspace_id']);

    $stmt = $conn->prepare("SELECT user_id FROM boards WHERE id = ?");
    $stmt->bind_param("i", $board_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $board = $result->fetch_assoc();
    $stmt->close();

    if ($board && $board['user_id'] == $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE boards SET is_archived = 1 WHERE id = ?");
        $stmt->bind_param("i", $board_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../dashboard.php?workspace_id=" . $workspace_id);
    exit();
}
