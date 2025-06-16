<?php
session_start();
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['workspace_id']);
    $name = trim($_POST['workspace_name']);

    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE workspaces SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: ../dashboard.php");
exit();
