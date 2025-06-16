<?php
require_once "../config.php";
require_once "add_notification.php";

$now = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));

$sql = "SELECT id, task_name, due_date, user_id, board_id FROM tasks 
        WHERE due_date IS NOT NULL 
        AND (due_date <= ?)
        AND status != 'Done'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tomorrow);
$stmt->execute();
$result = $stmt->get_result();

while ($task = $result->fetch_assoc()) {
    $task_id = $task['id'];
    $task_name = $task['task_name'];
    $due_date = $task['due_date'];
    $user_id = $task['user_id'];
    $board_id = $task['board_id'];

    if (empty($user_id)) continue;

    $message = (strtotime($due_date) < time()) ?
        "⚠️ Task '$task_name' is overdue!" :
        "⏰ Task '$task_name' is due soon.";

    add_notification($conn, $user_id, $task_id, $board_id, 'deadline', $message);
}
