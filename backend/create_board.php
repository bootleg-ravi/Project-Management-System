<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_name = trim($_POST['board_name']);
    $workspace_id = intval($_POST['workspace_id']);
    $user_id = $_SESSION['user_id'];

    if (!empty($board_name)) {
        $stmt = $conn->prepare("INSERT INTO boards (name, workspace_id, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $board_name, $workspace_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: /stratify/dashboard.php?workspace_id=$workspace_id");
    exit();
}
?>
