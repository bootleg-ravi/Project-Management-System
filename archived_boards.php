<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$workspace_id = isset($_GET['workspace_id']) ? intval($_GET['workspace_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM boards WHERE workspace_id = ? AND archived = 1");
$stmt->bind_param("i", $workspace_id);
$stmt->execute();
$archived_boards = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Archived Boards</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .archived-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        ul.archived-list {
            list-style: none;
            padding: 0;
        }

        ul.archived-list li {
            background: #f0f2f5;
            margin-bottom: 12px;
            padding: 12px 18px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul.archived-list li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        ul.archived-list li a:hover {
            text-decoration: underline;
        }

        form button {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        form button:hover {
            background-color: #218838;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0073ea;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="archived-container">
        <h2>📦 Archived Boards</h2>
        <?php if ($archived_boards->num_rows > 0): ?>
        <ul class="archived-list">
            <?php while ($board = $archived_boards->fetch_assoc()): ?>
                <li>
                    <span>📄 <?php echo htmlspecialchars($board['name']); ?></span>
                    <form action="/stratify/backend/restore_board.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="board_id" value="<?php echo $board['id']; ?>">
                        <input type="hidden" name="workspace_id" value="<?php echo $workspace_id; ?>">
                        <button type="submit">🔁 Restore</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No archived boards found for this workspace.</p>
        <?php endif; ?>

        <a class="back-link" href="dashboard.php?workspace_id=<?php echo $workspace_id; ?>">← Back to Dashboard</a>

        
    </div>
</body>
</html>
