<?php
session_start();
require_once "../config.php";

if (isset($_POST['workspace_id'])) {
    $id = intval($_POST['workspace_id']);
    $stmt = $conn->prepare("SELECT user_id FROM workspaces WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id != $_SESSION['user_id']) {
    header("Location: ../dashboard.php?error=unauthorized");
    exit();
}

    $conn->query("DELETE FROM boards WHERE workspace_id = $id");

    $stmt = $conn->prepare("DELETE FROM workspaces WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../dashboard.php");
exit();
