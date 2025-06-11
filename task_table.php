<?php
require_once "config.php";

$board_id = $_GET['board_id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM tasks WHERE board_id = ?");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
?>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Explanation</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['status']) ?></td>
            <td><?= nl2br(htmlspecialchars($task['explanation'] ?? '')) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
