<?php
session_start();
require_once "config.php";

if (!isset($_GET['id'])) {
    echo "No workspace selected.";
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT name, user_id FROM workspaces WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$workspace = $result->fetch_assoc();
$stmt->close();

if (!$workspace || $workspace['user_id'] != $_SESSION['user_id']) {
    echo "Unauthorized.";
    exit();
}
?>

<form id="editWorkspaceForm" action="backend/update_workspace.php" method="POST">
  <input type="hidden" name="workspace_id" value="<?php echo $id; ?>">
  <input type="text" name="workspace_name" value="<?php echo htmlspecialchars($workspace['name']); ?>" required>
  <button type="submit">Save Changes</button>
</form>
