<?php
session_start();
require_once "../config.php";
require_once(__DIR__ . '/../add_notification.php');

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$user_id = $_SESSION['user_id'];
$task_id = intval($_POST['task_id']);
$board_id = intval($_POST['board_id']);
$comment_text = trim($_POST['comment_text']);

if ($task_id > 0 && $comment_text !== '') {
    $stmt = $conn->prepare("INSERT INTO comments (task_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $task_id, $user_id, $comment_text);
    $stmt->execute();
    $stmt->close();

    preg_match_all('/@(\w+)/', $comment_text, $matches);
    $mentioned_usernames = $matches[1];

    foreach ($mentioned_usernames as $username) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE full_name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $mentioned_user_id = $row['id'];

            if ($mentioned_user_id != $user_id) {
                $message = "@$username, you were mentioned in a comment.";
                add_notification($conn, $mentioned_user_id, $task_id, $board_id, "Mention", $message);
            }
        }

        $stmt->close();
    }
}

header("Location: ../board_view.php?board_id=$board_id");
exit();
?>
