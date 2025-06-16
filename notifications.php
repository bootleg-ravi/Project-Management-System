<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Notifications - Stratify</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
 <style>
  * {
    box-sizing: border-box;
  }

  html, body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f8;
    width: 100%;
  }

  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    align-items: center; 
  }

  .dashboard {
    flex: 1;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .main-content {
    flex: 1;
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .notifications-container {
    width: 100%;
    max-width: 700px;
    margin: 50px 0;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  }

  .notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .notifications-header h2 {
    margin: 0;
    font-size: 1.6rem;
  }

  .notifications-header a {
    font-size: 0.9rem;
    text-decoration: none;
    color: #0073ea;
  }

  .notifications-header a:hover {
    text-decoration: underline;
  }

  .notification-item {
    border-left: 4px solid #0073ea;
    background: #f9fafc;
    border-radius: 8px;
    margin-bottom: 15px;
    padding: 15px 20px;
    transition: background 0.3s ease;
  }

  .notification-item:hover {
    background: #eef2f7;
  }

  .notification-item strong {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
  }

  .notification-item small {
    color: #888;
    font-size: 0.8rem;
    display: block;
    margin-top: 6px;
  }

  .no-notifications {
    text-align: center;
    color: #666;
    font-style: italic;
    margin-top: 30px;
  }

  footer {
    background: #f0f0f0;
    padding: 15px;
    text-align: center;
    font-size: 0.9rem;
    color: #555;
    border-top: 1px solid #ddd;
    width: 100%;
  }
</style>
</head>
<body>
  <div class="dashboard">
    <main class="main-content">
      <div class="notifications-container">
        <div class="notifications-header">
          <h2>Notifications</h2>
          <a href="dashboard.php">← Back to Dashboard</a>
        </div>

        <?php if ($notifications->num_rows === 0): ?>
          <p class="no-notifications">You have no notifications.</p>
        <?php else: ?>
          <?php while ($note = $notifications->fetch_assoc()): ?>
            <a href="board_view.php?board_id=<?php echo $note['board_id']; ?>&task_id=<?php echo $note['task_id']; ?>" style="text-decoration: none; color: inherit;">
              <div class="notification-item">
                <strong><?php echo htmlspecialchars($note['type']); ?></strong>
                <div><?php echo htmlspecialchars($note['message']); ?></div>
                <small><?php echo date("F j, Y, g:i a", strtotime($note['created_at'])); ?></small>
              </div>
            </a>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </main>
    <?php include 'partials/footer.php'; ?>
  </div>
</body>
</html>
