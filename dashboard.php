<?php
session_start();
require_once "config.php";

$workspace_id = $_GET['workspace_id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM boards WHERE workspace_id = ? AND archived = 0");
$stmt->bind_param("i", $workspace_id);
$stmt->execute();
$boards = $stmt->get_result();

$stmt = $conn->prepare("SELECT full_name, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

$display_name = $user['full_name'] ?? $_SESSION['full_name'] ?? 'User';
$avatar_path = $user['avatar'] ? 'uploads/avatars/' . htmlspecialchars($user['avatar']) : 'assets/img/default-avatar.png';

$stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$unread = $result->fetch_assoc()['unread_count'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - Stratify</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <script src="/stratify/assets/fullcalendar/index.global.min.js"></script>
  <style>
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      height: 100%;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .dashboard {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 60px);
    }

    .sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: #fff;
      padding: 20px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .brand {
      font-size: 1.4rem;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
    }

    .user-info {
      text-align: center;
      padding: 15px;
    }

    .user-info img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin-bottom: 10px;
      object-fit: cover;
      border: 2px solid #444;
    }

    .user-info a {
      font-weight: bold;
      color: #fff;
      text-decoration: none;
      display: block;
      margin-bottom: 8px;
    }

    .user-info a:hover {
      text-decoration: underline;
    }

    .menu h4 {
      margin-top: 0;
      margin-bottom: 10px;
    }

    .workspace-list ul,
    .board-list ul {
      list-style: none;
      padding-left: 0;
    }

    .workspace-list li a,
    .board-list li a {
      text-decoration: none;
      color: #333;
      padding: 8px 12px;
      display: block;
      border-radius: 6px;
      transition: background 0.2s ease;
    }

    .workspace-list li a:hover,
    .board-list li a:hover {
      background: #e2e8f0;
    }

    .create-workspace-form input[type="text"],
    form input[type="text"] {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 100%;
      margin-bottom: 10px;
    }

    form button {
      padding: 8px 12px;
      background: #0073ea;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
    }

    form button:hover {
      background: #005bb5;
    }

    .logout-btn {
      background: #e74c3c;
      color: white;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      text-decoration: none;
      display: block;
      margin-top: 20px;
    }

    .logout-btn:hover {
      background: #c0392b;
    }

    .main-content {
      flex: 1;
      padding: 20px;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .topbar h2 {
      margin: 0;
      font-size: 1.4rem;
    }

    .notification-icon a {
      text-decoration: none;
      color: #0073ea;
      font-weight: bold;
    }

    .notification-icon a:hover {
      text-decoration: underline;
    }

    .board-list {
      margin-top: 20px;
    }

    .no-selection {
      margin-top: 40px;
      font-size: 1rem;
      color: #666;
    }

    footer {
      background: #f0f0f0;
      padding: 15px;
      text-align: center;
      font-size: 0.9rem;
      color: #555;
      border-top: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <aside class="sidebar">
      <div>
        <div class="brand">Stratify</div>
        <div class="user-info">
  <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" style="text-decoration: none; color: inherit;">
    <div style="text-align: center;">
      <img src="<?php echo $avatar_path; ?>" alt="User Avatar" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 8px; object-fit: cover; border: 2px solid #444;" />
      <div style="font-weight: bold; color: white;"><?php echo htmlspecialchars($display_name); ?></div>
    </div>
  </a>
</div>
        <div class="menu workspace-list">
          <h4>My Workspaces</h4>
          <ul>
  <?php
  $query = "SELECT workspaces.*, users.full_name AS owner_name 
            FROM workspaces 
            JOIN users ON workspaces.user_id = users.id";
  $result = $conn->query($query);
  while ($ws = $result->fetch_assoc()):
      $ws_id = $ws['id'];
      $ws_name = htmlspecialchars($ws['name']);
      $ws_owner = htmlspecialchars($ws['owner_name']);
  ?>
    <li style="margin-bottom: 8px;">
      <div style="display: flex; flex-direction: column;">
        <a href="?workspace_id=<?php echo $ws_id; ?>">
          📁 <?php echo $ws_name; ?> (by <?php echo $ws_owner; ?>)
        </a>
        <?php if ($ws['user_id'] == $_SESSION['user_id']): ?>
          <div style="display: flex; gap: 8px; margin-top: 4px; font-size: 0.85rem;">
          <a href="#" style="color: #ccc;" onclick="openEditWorkspaceModal(<?php echo $ws_id; ?>); return false;">✏️ Edit</a>
            <form action="backend/delete_workspace.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this workspace?');" style="display:inline;">
              <input type="hidden" name="workspace_id" value="<?php echo $ws_id; ?>">
              <button type="submit" style="background:none; border:none; color:#f88; cursor:pointer; padding:0;">🗑️ Delete</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </li>
  <?php endwhile; ?>
</ul>
          <form action="backend/create_workspace.php" method="POST" class="create-workspace-form">
            <input type="text" name="workspace_name" placeholder="New Workspace" required>
            <button type="submit">+ Create</button>
          </form>
        </div>
      </div>
      <div>
        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </aside>

    <main class="main-content">
    <header class="topbar">
  <h2>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h2>
  <div class="notification-icon">
    <a href="notifications.php">🔔 <?php echo $unread > 0 ? "($unread)" : ""; ?></a>
  </div>
</header>

<div class="calendar-container">
  <div id="calendar"></div>
</div>

<style>
  .calendar-container {
    max-width: 750px;
    margin: 20px auto;
    padding: 10px;
    background: transparent;
    border-radius: 6px;
    box-shadow: 0 0px 0px rgba(0,0,0,0.1);
  }
  #calendar {
    width: 100%;
    min-height: 50px;
  }
</style>

<?php if (isset($_GET['workspace_id'])):
    $workspace_id = intval($_GET['workspace_id']);

    $stmt = $conn->prepare("SELECT COUNT(*) AS total_boards FROM boards WHERE workspace_id = ? AND archived = 0");
$stmt->bind_param("i", $workspace_id);
$stmt->execute();
$result = $stmt->get_result();
$total_boards = $result->fetch_assoc()['total_boards'] ?? 0;
$stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS due_tasks FROM tasks 
        WHERE board_id IN (SELECT id FROM boards WHERE workspace_id = ?) 
        AND due_date IS NOT NULL AND due_date <= CURDATE() AND status != 'Done'");
    $stmt->bind_param("i", $workspace_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $due_tasks = $result->fetch_assoc()['due_tasks'] ?? 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS done_tasks FROM tasks 
        WHERE board_id IN (SELECT id FROM boards WHERE workspace_id = ?) 
        AND status = 'Done'");
    $stmt->bind_param("i", $workspace_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $done_tasks = $result->fetch_assoc()['done_tasks'] ?? 0;
    $stmt->close();
?>
<section class="dashboard-summary">
  <div class="summary-card">
    <div class="summary-icon">📁</div>
    <div>
      <h4>Total Boards</h4>
      <p><?php echo $total_boards; ?></p>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-icon">📅</div>
    <div>
      <h4>Due Tasks</h4>
      <p><?php echo $due_tasks; ?></p>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-icon">✅</div>
    <div>
      <h4>Completed Tasks</h4>
      <p><?php echo $done_tasks; ?></p>
    </div>
  </div>
</section>
<?php endif; ?>

      <?php if (isset($_GET['workspace_id'])):
          $workspace_id = intval($_GET['workspace_id']);
          $stmt = $conn->prepare("SELECT * FROM boards WHERE workspace_id = ? AND archived = 0");
          $stmt->bind_param("i", $workspace_id);
          $stmt->execute();
          $boards = $stmt->get_result();
      ?>
        <section class="board-list">
          <h3>📋 Boards in This Workspace</h3>

          <ul>
  <?php while ($board = $boards->fetch_assoc()): ?>
    <li style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <a href="board_view.php?board_id=<?php echo $board['id']; ?>">
          📄 <?php echo htmlspecialchars($board['name']); ?>
        </a>
      </div>

      <div style="display: flex; gap: 10px;">
      <form action="/stratify/backend/archive_board.php" method="POST" onsubmit="return confirm('Archive this board?');">
      <input type="hidden" name="board_id" value="<?php echo $board['id']; ?>">
  <input type="hidden" name="workspace_id" value="<?php echo $workspace_id; ?>">
  <button type="submit" class="archive-btn">📦 Archive</button>
</form>

<form action="/stratify/backend/delete_board.php" method="POST" onsubmit="return confirm('Delete this board permanently?');">
  <input type="hidden" name="board_id" value="<?php echo $board['id']; ?>">
  <input type="hidden" name="workspace_id" value="<?php echo $workspace_id; ?>">
  <button type="submit" class="delete-btn">🗑️ Delete</button>
</form>
      </div>
    </li>
  <?php endwhile; ?>
</ul>

<a href="archived_boards.php?workspace_id=<?= $workspace_id ?>">📦 View Archived Boards</a>

          <form action="backend/create_board.php" method="POST">
            <input type="hidden" name="workspace_id" value="<?php echo $workspace_id; ?>">
            <input type="text" name="board_name" placeholder="New Board" required>
            <button type="submit">+ Add Board</button>
          </form>
        </section>
      <?php else: ?>
        <p class="no-selection">Select a workspace from the left to view and manage its boards.</p>
      <?php endif; ?>
    </main>
  </div>
  <script>
function openEditWorkspaceModal(workspaceId) {
  document.getElementById('modalBackdrop').style.display = 'block';
  document.getElementById('modalContainer').style.display = 'block';

  fetch(`edit_workspace_form.php?id=${workspaceId}`)
    .then(response => response.text())
    .then(html => {
      document.getElementById('modalContent').innerHTML = html;

      const form = document.getElementById('editWorkspaceForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();

          const formData = new FormData(form);
          fetch(form.action, {
            method: 'POST',
            body: formData
          })
          .then(res => res.text())
          .then(data => {
            alert('Workspace updated!');
            closeModal();
            location.reload();
          })
          .catch(() => alert('Error updating workspace.'));
        });
      }
    });
}
</script>

<div id="modalBackdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9998;"></div>

<div id="modalContainer" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
  background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); z-index:9999; max-width:400px; width:90%;">
  <button id="closeModalBtn" style="float:right; background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
  <div id="modalContent"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: '/stratify/backend/get_deadlines.php',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: ''
      },
      height: 'auto'
    });

    calendar.render();
  });
</script>


  <footer>
    &copy; <?php echo date("Y"); ?> Stratify. All rights reserved.
  </footer>
</body>
</html>
