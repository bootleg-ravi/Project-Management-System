<?php
session_start();
require_once "config.php";

$highlight_task = isset($_GET['highlight_task']) ? intval($_GET['highlight_task']) : 0;

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$board_id = isset($_GET['board_id']) ? intval($_GET['board_id']) : 0;
if ($board_id <= 0) {
    die("Invalid board ID.");
}

$stmt = $conn->prepare("SELECT name FROM boards WHERE id = ?");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$board_result = $stmt->get_result();
$board = $board_result->fetch_assoc();
if (!$board) {
    die("Board not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($board['name']) ?> - Stratify</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f9fafc;
  }

  .dashboard {
    display: flex;
  }

  .sidebar {
    width: 190px;
    background-color: #1f2937;
    color: #fff;
    padding: 20px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  .sidebar .brand {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 40px;
    color: #60a5fa;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: auto;
  }

  .sidebar-link {
    color: #d1d5db;
    text-decoration: none;
    font-size: 0.95rem;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background 0.2s ease;
  }

  .sidebar-link:hover {
    background-color: #374151;
    color: #fff;
  }

  .main-content {
    margin-left: 220px;
    padding: 30px;
    flex-grow: 1;
  }

  .topbar {
    background: #ffffff;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
    border-radius: 8px;
  }

  .board-title {
  margin: 0;
  font-size: 1.5rem;
  color: #1f2937;
  font-weight: 600;
}

  .kanban-board {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 30px;
  }

  .kanban-column {
    flex: 1;
    min-width: 280px;
    border-radius: 8px;
    padding: 10px;
  }

  .kanban-column[data-status="To Do"] {
    background-color: #e7f1ff;
  }

  .kanban-column[data-status="In Progress"] {
    background-color: #fff3cd;
  }

  .kanban-column[data-status="Done"] {
    background-color: #d1e7dd;
  }

  .kanban-column h3 {
    font-size: 1.1rem;
    text-align: center;
    margin-bottom: 10px;
  }

  .task-card {
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 6px;
    cursor: grab;
  }

  .task-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  .task-table th, .task-table td {
    padding: 10px;
    border: 1px solid #ddd;
  }

  .badge-grey,
  .badge-yellow,
  .badge-green,
  .badge-orange,
  .badge-red {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-right: 5px;
    vertical-align: middle;
    white-space: nowrap;
  }

  .badge-grey { background: #ccc; color: #333; }
  .badge-yellow { background: #f4c542; color: #000; }
  .badge-green { background: #5cb85c; color: #fff; }
  .badge-orange { background: #f0ad4e; color: #fff; }
  .badge-red { background: #d9534f; color: #fff; }

  .task-form {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 30px;
  }

  .task-form input,
  .task-form select,
  .task-form textarea {
    padding: 8px;
    margin: 5px 0;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  .task-form button {
    padding: 10px 15px;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }

  .task-form button:hover {
    background: #0056b3;
  }

  #explanationModal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: #00000088;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  #explanationModal > div {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
  }

  #explanationModal span {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
  }

  .back-link {
  color: #9ca3af;
  text-decoration: none;
  font-size: 0.95rem;
  margin-bottom: 20px;
  display: block;
  transition: color 0.2s ease;
}

.back-link:hover {
  color: #ffffff;
}

.comments-section {
  margin-top: 1em;
  border-top: 1px solid #ddd;
  padding-top: 1em;
  max-height: 200px;
  overflow-y: auto;
}

.comment {
  margin-bottom: 1em;
  padding-bottom: 0.5em;
  border-bottom: 1px solid #eee;
}

.comment strong a {
  color: #2a7ae2;
  text-decoration: none;
}

.comment small {
  color: #999;
  margin-left: 0.5em;
  font-size: 0.85em;
}

.mention {
  color: #1a73e8;
  font-weight: bold;
  text-decoration: none;
}

.mention:hover {
  text-decoration: underline;
}

#mention-suggestions .suggestion-item {
  padding: 5px 10px;
  cursor: pointer;
}
#mention-suggestions .suggestion-item:hover {
  background-color: #eee;
}

.highlighted-task {
    background-color: #fffae6;
    border: 2px solid #ffcc00;
    transition: background-color 2s ease;
  }
  
    .footer {
      margin-left: 220px;
      padding: 15px 30px;
      background: #f3f4f6;
      text-align: center;
      font-size: 0.9rem;
      color: #777;
      border-top: 1px solid #e5e7eb;
    }
</style>
</head>
<body>
<div class="dashboard">
  <aside class="sidebar">
    <div class="brand">Stratify</div>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
  </aside>

<main class="main-content">
  <header class="topbar">
    <h2 class="board-title"><?= htmlspecialchars($board['name']) ?> – Tasks</h2>
  </header>

    <section class="task-form">
      <form action="backend/add_task.php" method="POST">
        <input type="hidden" name="board_id" value="<?= $board_id ?>">
        <input type="text" name="task_name" placeholder="Task name" required>
        <textarea name="explanation" placeholder="Task explanation"></textarea>
        <select name="priority" required>
          <option value="">Select Priority</option>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
        <input type="date" name="due_date" required>
        <select name="user_id">
          <option value="">Assign to...</option>
          <?php
          $user_result = $conn->query("SELECT id, full_name FROM users");
          while ($user = $user_result->fetch_assoc()) {
              echo "<option value='{$user['id']}'>" . htmlspecialchars($user['full_name']) . "</option>";
          }
          ?>
        </select>
        <button type="submit">➕ Add Task</button>
      </form>
    </section>

    <section class="board-view">
      <div class="kanban-board" id="kanbanBoard">
        <?php
        $columns = ['To Do', 'In Progress', 'Done'];
        foreach ($columns as $column) {
            echo "<div class='kanban-column' ondrop='drop(event)' ondragover='allowDrop(event)' data-status='$column'>";
            echo "<h3>$column</h3>";

            $stmt = $conn->prepare("SELECT * FROM tasks WHERE board_id = ? AND status = ?");
            $stmt->bind_param("is", $board_id, $column);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($task = $result->fetch_assoc()) {
                $task_id = $task['id'];
                $name = htmlspecialchars($task['task_name']);
                $priority = badge($task['priority'], 'priority');
                $due = htmlspecialchars($task['due_date']);
                $explanation = htmlspecialchars($task['explanation']);
                $user_result = $conn->query("SELECT full_name FROM users WHERE id = " . intval($task['user_id']));
                $assigned_to = $user_result->fetch_assoc()['full_name'] ?? 'Unassigned';

                echo "<div class='task-card'
                draggable='true'
                ondragstart='drag(event)'
                onclick=\"openModal('" . addslashes($name) . "', '" . addslashes($explanation) . "', " . $task_id . ")\"
                id='task-$task_id'
                data-id='$task_id'>
                <strong>$name</strong><br>
                $priority<br>
                Due: $due<br>
                <small>Assigned: $assigned_to</small>
              </div>";        
            }

            echo "</div>";
        }

        function badge($text, $type) {
            $classes = [
                'status' => [
                    'To Do' => 'badge-grey',
                    'In Progress' => 'badge-yellow',
                    'Done' => 'badge-green',
                ],
                'priority' => [
                    'Low' => 'badge-green',
                    'Medium' => 'badge-orange',
                    'High' => 'badge-red',
                ]
            ];
            $class = $classes[$type][$text] ?? 'badge-grey';
            return "<span class='badge $class'>" . htmlspecialchars($text) . "</span>";
        }
        ?>
      </div>
    </section>

    <section class="task-list">
      <table class="task-table">
        <thead>
          <tr>
            <th>Task</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Due Date</th>
            <th>Assigned To</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE board_id = ?");
        $stmt->bind_param("i", $board_id);
        $stmt->execute();
        $tasks = $stmt->get_result();

        while ($task = $tasks->fetch_assoc()) {
          $user_result = $conn->query("SELECT full_name FROM users WHERE id = " . intval($task['user_id']));
          $user = $user_result->fetch_assoc();
          $assigned_to = $user['full_name'] ?? 'Unassigned';

          echo "<tr>
            <td>" . htmlspecialchars($task['task_name']) . "</td>
            <td>" . badge($task['status'], 'status') . "</td>
            <td>" . badge($task['priority'], 'priority') . "</td>
            <td>" . htmlspecialchars($task['due_date']) . "</td>
            <td>" . htmlspecialchars($assigned_to) . "</td>
            <td>
              <a href='edit_task.php?id={$task['id']}&board_id=$board_id'>✏️ Edit</a>
              <a href='backend/delete_task.php?id={$task['id']}&board_id=$board_id' onclick=\"return confirm('Delete this task?')\">🗑️ Delete</a>
            </td>
          </tr>";
        }
        ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<div id="explanationModal">
  <div>
    <span onclick="closeModal()">×</span>
    <h3 id="modalTitle">Task</h3>
    <p id="modalContent">Explanation</p>

    <div id="commentSection"></div>

    <form id="commentForm" action="backend/add_comment.php" method="POST" style="margin-top: 1em;">
      <input type="hidden" name="task_id" id="commentTaskId">
      <input type="hidden" name="board_id" value="<?= $board_id ?>">
      <textarea id="comment_text" name="comment_text" rows="4" cols="50" placeholder="Add comment..." required style="width: 100%;"></textarea>
      <div id="mention-suggestions" style="border:1px solid #ccc; display:none; position:absolute; background:#fff; z-index:1000;"></div>
      <button type="submit" style="margin-top: 0.5em;">Comment</button>
    </form>
  </div>
</div>


<script>
  let dragged;

  function allowDrop(ev) {
    ev.preventDefault();
  }

  function drag(ev) {
    dragged = ev.target;
  }

  function drop(ev) {
    ev.preventDefault();
    const newStatus = ev.currentTarget.getAttribute("data-status");
    const taskId = dragged.getAttribute("data-id");
    ev.currentTarget.appendChild(dragged);

    fetch("backend/update_status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${taskId}&status=${encodeURIComponent(newStatus)}`
    }).then(() => location.reload());
  }

  function openModal(title, explanation, taskId) {
  document.getElementById("modalTitle").innerText = title;
  document.getElementById("modalContent").innerText = explanation;
  document.getElementById("commentTaskId").value = taskId;
  document.getElementById("explanationModal").style.display = "flex";

  fetch(`backend/get_comments.php?task_id=${taskId}`)
    .then(response => response.text())
    .then(html => {
      document.getElementById("commentSection").innerHTML = html;
    });
}


  function closeModal() {
    document.getElementById("explanationModal").style.display = "none";
  }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
  let $textarea = $('#comment_text');
  let $suggestions = $('#mention-suggestions');

  $textarea.on('keyup', function(e) {
    let cursorPos = this.selectionStart;
    let text = $textarea.val().slice(0, cursorPos);

    let match = text.match(/@(\w*)$/);
    if (match) {
      let query = match[1];
      if (query.length >= 1) {
        $.getJSON('backend/search_users.php', { q: query }, function(data) {
          if (data.length > 0) {
            let suggestionsHtml = '';
            data.forEach(user => {
              suggestionsHtml += `<div class="suggestion-item" data-username="${user.full_name}">${user.full_name}</div>`;
            });
            $suggestions.html(suggestionsHtml).show().css({
              top: $textarea.offset().top + $textarea.outerHeight(),
              left: $textarea.offset().left,
              width: $textarea.outerWidth()
            });
          } else {
            $suggestions.hide();
          }
        });
      } else {
        $suggestions.hide();
      }
    } else {
      $suggestions.hide();
    }
  });

  $suggestions.on('click', '.suggestion-item', function() {
    let username = $(this).data('username');
    let cursorPos = $textarea[0].selectionStart;
    let text = $textarea.val();
    let beforeCursor = text.slice(0, cursorPos);
    let afterCursor = text.slice(cursorPos);

    let newBeforeCursor = beforeCursor.replace(/@(\w*)$/, '@' + username + ' ');

    $textarea.val(newBeforeCursor + afterCursor);
    $textarea.focus();

    $suggestions.hide();
  });

  $(document).click(function(e) {
    if (!$(e.target).closest('#mention-suggestions, #comment_text').length) {
      $suggestions.hide();
    }
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const highlightTaskId = <?= $highlight_task ?>;
  if (highlightTaskId) {
    const taskElem = document.querySelector(`[data-id='${highlightTaskId}']`);
    if (taskElem) {

      taskElem.scrollIntoView({ behavior: 'smooth', block: 'center' });

      taskElem.classList.add('highlighted-task');
      setTimeout(() => {
        taskElem.classList.remove('highlighted-task');
      }, 3000);

      </script>
      </main>
  </div>
  <footer class="footer">
    Stratify &copy; <?php echo date('Y'); ?>. All rights reserved.
  </footer>
</body>
</html>

