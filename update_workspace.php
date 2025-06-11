<?php
session_start();
require_once "../config.php";

if (isset($_POST['workspace_id'], $_POST['workspace_name'])) {
    $id = intval($_POST['workspace_id']);
    $name = trim($_POST['workspace_name']);

    $stmt = $conn->prepare("SELECT user_id FROM workspaces WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($owner_id);
    $stmt->fetch();
    $stmt->close();

    if ($owner_id == $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE workspaces SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: ../dashboard.php");
exit();
