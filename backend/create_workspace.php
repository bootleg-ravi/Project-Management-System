<?php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workspace_name = trim($_POST['workspace_name']);
    $user_id = $_SESSION['user_id'];

    if (!empty($workspace_name)) {
        $stmt = $conn->prepare("INSERT INTO workspaces (user_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $workspace_name);
        $stmt->execute();
    }
    header("Location: /stratify/dashboard.php");
    exit();
}
?>
