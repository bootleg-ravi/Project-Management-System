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

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Workspace</title>
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
    }

    .modal-container {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      margin: 50px auto;
    }

    h2 {
      margin-top: 0;
      font-size: 1.2rem;
      color: #333;
    }

    form input[type="text"] {
      padding: 10px;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    button {
      background-color: #0073ea;
      color: #fff;
      padding: 10px 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
    }

    button:hover {
      background-color: #005bb5;
    }
  </style>
</head>
<body>
  <div class="modal-container">
    <h2>Rename Workspace</h2>
    <form action="backend/update_workspace.php" method="POST">
      <input type="hidden" name="workspace_id" value="<?php echo $id; ?>">
      <input type="text" name="workspace_name" value="<?php echo htmlspecialchars($workspace['name']); ?>" required>
      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>
</html>
