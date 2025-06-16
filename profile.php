<?php
session_start();
require_once "config.php";

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['user_id'] ?? null);
if (!$profile_id) die("No profile ID and no user logged in.");

$is_own_profile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_id;

$stmt = $conn->prepare("SELECT id, full_name, bio, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) die("User not found.");

$upload_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    $new_name = trim($_POST['full_name']);
    $new_bio = trim($_POST['bio']);

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024;

        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_size = $_FILES['avatar']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('avatar_', true) . '.' . $ext;
            $target = "uploads/avatars/" . $filename;
            if (!file_exists("uploads/avatars/")) mkdir("uploads/avatars/", 0777, true);
            if (move_uploaded_file($file_tmp, $target)) {
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->bind_param("si", $filename, $profile_id);
                $stmt->execute();
                $stmt->close();
                $user['avatar'] = $filename;
            } else {
                $upload_error = "Avatar upload failed.";
            }
        } else {
            $upload_error = "Invalid avatar (JPG, PNG, GIF, max 2MB).";
        }
    }

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_name, $new_bio, $profile_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['full_name'] = $new_name;
    $user['full_name'] = $new_name;
    $user['bio'] = $new_bio;

    header("Location: profile.php?id=" . $profile_id);
    exit();
}

$stats = [
    'tasks' => $conn->query("SELECT COUNT(*) AS count FROM tasks WHERE user_id = $profile_id")->fetch_assoc()['count'] ?? 0,
    'comments' => $conn->query("SELECT COUNT(*) AS count FROM comments WHERE user_id = $profile_id")->fetch_assoc()['count'] ?? 0,
];

$username_map = [];
$res = $conn->query("SELECT id, full_name FROM users");
while ($row = $res->fetch_assoc()) {
    $username_map[$row['full_name']] = $row['id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>@<?php echo htmlspecialchars($user['full_name']); ?> - Profile</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <styl>
   <style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background-color: #f9fafb;
    color: #333;
  }

  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

.content-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: stretch; 
  width: 100%;
}


.main-content {
  padding: 30px;
  margin: 0 auto;
  max-width: 860px;
  box-sizing: border-box;
}

  .profile-container {
    max-width: 760px;
    margin: 30px auto;
    padding: 24px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
  }

  .profile-avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
    margin-bottom: 15px;
    transition: transform 0.3s ease;
  }

  .profile-avatar:hover {
    transform: scale(1.05);
  }

  .tabs {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 25px;
    flex-wrap: wrap;
  }

  .tabs button {
    padding: 10px 20px;
    border: 1px solid transparent;
    background: #f1f5f9;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 15px;
  }

  .tabs button:hover {
    background: #e2e8f0;
  }

  .tabs button.active {
    background: #3b82f6;
    color: #fff;
    font-weight: 600;
    border-color: #3b82f6;
  }

  .tab-content {
    display: none;
    margin-top: 20px;
  }

  .tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .edit-form label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
  }

  .edit-form input,
  .edit-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 14px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 14px;
  }

  .edit-form input[type="file"] {
    padding: 8px;
  }

  .edit-form button {
    padding: 10px 20px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s ease;
  }

  .edit-form button:hover {
    background: #2563eb;
  }

  .tab-content ul {
    list-style-type: disc;
    padding-left: 20px;
  }

  .tab-content ul li {
    margin-bottom: 6px;
  }

  a {
    color: #3b82f6;
    text-decoration: none;
  }

  a:hover {
    text-decoration: underline;
  }

  footer {
    background: #f5f5f5;
    text-align: center;
    padding: 12px 20px;
    font-size: 14px;
    border-top: 1px solid #ddd;
  }
  </style>
</head>
<body>
<div class="content-wrapper">
  <div class="dashboard">
    <main class="main-content">
      <header class="topbar">
        <h2>@<?php echo htmlspecialchars($user['full_name']); ?>'s Profile</h2>
      </header>

      <a href="dashboard.php">← Back to Dashboard</a>

      <section class="profile-container">
        <div style="text-align:center">
          <img src="<?php echo $user['avatar'] ? 'uploads/avatars/' . htmlspecialchars($user['avatar']) : 'assets/img/default-avatar.png'; ?>" class="profile-avatar" alt="Avatar">
          <h3>@<?php echo htmlspecialchars($user['full_name']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio yet.')); ?></p>
        </div>

        <?php if ($upload_error): ?>
          <p style="color: red; text-align:center;"><?php echo $upload_error; ?></p>
        <?php endif; ?>

        <div class="tabs">
          <button class="tab-btn active" data-tab="overview">Overview</button>
          <button class="tab-btn" data-tab="tasks">Tasks</button>
          <button class="tab-btn" data-tab="comments">Comments</button>
          <?php if ($is_own_profile): ?>
            <button class="tab-btn" data-tab="edit">Edit</button>
          <?php endif; ?>
        </div>

        <div id="overview" class="tab-content active" style="text-align: center;">
  <p><strong>Tasks:</strong> <?php echo $stats['tasks']; ?></p>
  <p><strong>Comments:</strong> <?php echo $stats['comments']; ?></p>
</div>

        <div id="tasks" class="tab-content">
          <ul>
            <?php
            $res = $conn->query("SELECT task_name FROM tasks WHERE user_id = $profile_id ORDER BY id DESC LIMIT 10");
            while ($r = $res->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($r['task_name']) . "</li>";
            }
            ?>
          </ul>
        </div>

        <div id="comments" class="tab-content">
          <ul>
            <?php
            $res = $conn->query("SELECT comment_text FROM comments WHERE user_id = $profile_id ORDER BY id DESC LIMIT 10");
            while ($r = $res->fetch_assoc()) {
                $text = htmlspecialchars($r['comment_text']);
                $text = preg_replace_callback('/@([a-zA-Z0-9_. -]+)/', function ($m) use ($username_map) {
                    $u = $m[1];
                    return isset($username_map[$u]) ? "<a href='profile.php?id={$username_map[$u]}'>@$u</a>" : "@$u";
                }, $text);
                echo "<li>" . nl2br($text) . "</li>";
            }
            ?>
          </ul>
        </div>

        <?php if ($is_own_profile): ?>
        <div id="edit" class="tab-content">
          <form method="POST" enctype="multipart/form-data" class="edit-form">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label>Bio:</label>
            <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

            <label>Avatar:</label>
            <input type="file" name="avatar" accept="image/*">

            <button type="submit">Save Changes</button>
          </form>
        </div>
        <?php endif; ?>
      </section>
    </main>
  </div>
</div>

<?php include 'partials/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab-btn");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach(btn => btn.addEventListener("click", () => {
    tabs.forEach(b => b.classList.remove("active"));
    contents.forEach(c => c.classList.remove("active"));
    btn.classList.add("active");
    document.getElementById(btn.dataset.tab).classList.add("active");
  }));
});
</script>
</body>
</html>
