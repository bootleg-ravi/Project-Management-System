<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['board_id'])) {
    die("Task or board ID missing.");
}

$task_id = intval($_GET['id']);
$board_id = intval($_GET['board_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?? null;
    $assigned_user = !empty($_POST['assigned_user']) ? intval($_POST['assigned_user']) : null;
    $explanation = trim($_POST['explanation']);

    if (!empty($task_name)) {
        $stmt = $conn->prepare("UPDATE tasks SET task_name=?, status=?, priority=?, due_date=?, user_id=?, explanation=? WHERE id=?");
        $stmt->bind_param("ssssisi", $task_name, $status, $priority, $due_date, $assigned_user, $explanation, $task_id);
        if ($stmt->execute()) {
            header("Location: board_view.php?board_id=$board_id");
            exit();
        } else {
            $error = "Failed to update task.";
        }
    } else {
        $error = "Task name cannot be empty.";
    }
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    die("Task not found.");
}

$users_result = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Task - <?= htmlspecialchars($task['task_name']) ?></title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <style>
    form {
      max-width: 500px;
      margin: 40px auto;
      background: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input[type="text"],
    input[type="date"],
    select,
    textarea {
      width: 100%;
      padding: 8px;
      margin-top: 6px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 1rem;
      resize: vertical;
    }
    textarea {
      min-height: 100px;
    }
    .btn-submit {
      margin-top: 20px;
      background-color: #5cb85c;
      border: none;
      padding: 12px 20px;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1rem;
    }
    .btn-submit:hover {
      background-color: #4cae4c;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <form method="POST" action="">
    <h2>Edit Task</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label for="task_name">Task Name</label>
    <input type="text" name="task_name" id="task_name" value="<?= htmlspecialchars($task['task_name']) ?>" required />

    <label for="status">Status</label>
    <select name="status" id="status" required>
      <?php
      $statuses = ['To Do', 'In Progress', 'Done'];
      foreach ($statuses as $s) {
          $sel = ($task['status'] === $s) ? 'selected' : '';
          echo "<option value='$s' $sel>$s</option>";
      }
      ?>
    </select>

    <label for="priority">Priority</label>
    <select name="priority" id="priority" required>
      <?php
      $priorities = ['Low', 'Medium', 'High'];
      foreach ($priorities as $p) {
          $sel = ($task['priority'] === $p) ? 'selected' : '';
          echo "<option value='$p' $sel>$p</option>";
      }
      ?>
    </select>

    <label for="due_date">Due Date</label>
    <input type="date" name="due_date" id="due_date" value="<?= htmlspecialchars($task['due_date']) ?>" />

    <label for="assigned_user">Assign To</label>
    <select name="assigned_user" id="assigned_user">
      <option value="">-- Unassigned --</option>
      <?php while ($user = $users_result->fetch_assoc()): 
          $sel = ($task['user_id'] == $user['id']) ? 'selected' : '';
      ?>
        <option value="<?= $user['id'] ?>" <?= $sel ?>><?= htmlspecialchars($user['full_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="explanation">Task Explanation</label>
    <textarea name="explanation" id="explanation"><?= htmlspecialchars($task['explanation']) ?></textarea>

    <button type="submit" class="btn-submit">Save Changes</button>
  </form>


<div id="commentModal" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; padding:20px; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.3); z-index:1000;">
  <h3>Comments</h3>
  <div id="commentsList" style="max-height:200px; overflow-y:auto; border:1px solid #ddd; padding:10px; margin-bottom:10px;">
  </div>

  <form id="commentForm">
    <input type="hidden" name="task_id" value="<?= intval($_GET['id'] ?? 0) ?>">
    <input type="hidden" name="board_id" value="<?= intval($_GET['board_id'] ?? 0) ?>">
    <textarea id="commentText" name="comment_text" rows="3" style="width:100%;" placeholder="Write a comment..."></textarea><br>
    <button type="submit">Submit</button>
    <button type="button" id="closeCommentModalBtn">Close</button>
  </form>
</div>

<div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:999;"></div>

<script>
const openBtn = document.getElementById("openCommentModalBtn");
const closeBtn = document.getElementById("closeCommentModalBtn");
const modal = document.getElementById("commentModal");
const overlay = document.getElementById("modalOverlay");
const commentsList = document.getElementById("commentsList");
const commentForm = document.getElementById("commentForm");

function openModal() {
  modal.style.display = "block";
  overlay.style.display = "block";
  loadComments(commentForm.task_id.value);
}

function closeModal() {
  modal.style.display = "none";
  overlay.style.display = "none";
}

openBtn.addEventListener("click", openModal);
closeBtn.addEventListener("click", closeModal);
overlay.addEventListener("click", closeModal);

commentForm.addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(commentForm);

  fetch("backend/add_comment.php", { 
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
      commentForm.comment_text.value = "";
      loadComments(formData.get("task_id"));
    } else if(data.error){
      alert(data.error);
    } else {
      alert("Failed to add comment.");
    }
  })
  .catch(() => alert("Error adding comment."));
});

function loadComments(taskId) {
  fetch(`backend/get_comments.php?task_id=${taskId}`)
    .then(res => res.json())
    .then(data => {
      commentsList.innerHTML = "";
      if(data.length === 0) {
        commentsList.innerHTML = "<i>No comments yet.</i>";
        return;
      }
      data.forEach(c => {
        const date = new Date(c.created_at);
        const dateStr = date.toLocaleString();

        let text = c.comment_text.replace(/@([\w]+)/g, `<span style="color:blue;">@$1</span>`);

        commentsList.innerHTML += `
          <div style="margin-bottom:8px;">
            <b>${c.full_name}</b> <small style="color:#666;">${dateStr}</small><br>
            <span>${text}</span>
          </div>
        `;
      });
    })
    .catch(() => {
      commentsList.innerHTML = "<i>Failed to load comments.</i>";
    });
}
</script>
</body>
</html>
