<?php
session_start();
require_once "../config.php";

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

$comment_sql = "
    SELECT comments.comment_text, comments.created_at, users.id AS user_id, users.full_name
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.task_id = ?
    ORDER BY comments.created_at ASC
";

$stmt = $conn->prepare($comment_sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

function linkify_mentions($text, $conn) {
    return preg_replace_callback('/@([\w]+)/', function($matches) use ($conn) {
        $full_name = $matches[1];
        $stmt = $conn->prepare("SELECT id FROM users WHERE full_name = ?");
        $stmt->bind_param("s", $full_name);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $user_id = $row['id'];
            $stmt->close();
            return '<a href="profile.php?id=' . intval($user_id) . '" class="mention">@' . htmlspecialchars($full_name) . '</a>';
        } else {
            $stmt->close();
            return '@' . htmlspecialchars($full_name);
        }
    }, htmlspecialchars($text));
}

echo '<div class="comments-section">';
while ($comment = $result->fetch_assoc()) {
    $comment_html = linkify_mentions($comment['comment_text'], $conn);
    echo '<div class="comment">';
    echo '<strong><a href="profile.php?id=' . intval($comment['user_id']) . '">' . htmlspecialchars($comment['full_name']) . '</a></strong> ';
    echo '<small>' . htmlspecialchars(date("Y-m-d H:i", strtotime($comment['created_at']))) . '</small>';
    echo '<p>' . nl2br($comment_html) . '</p>';
    echo '</div>';
}
echo '</div>';

$stmt->close();
?>
