<?php
function add_notification($conn, $user_id, $task_id, $board_id, $type, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, task_id, board_id, type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $user_id, $task_id, $board_id, $type, $message);
    $stmt->execute();
    $stmt->close();
}
?>
