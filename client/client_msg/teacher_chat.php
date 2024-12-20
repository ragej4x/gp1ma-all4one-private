<?php
include '../../php/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login_teacher.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users");
$stmt->execute();
$students = $stmt->fetchAll();

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

$messages = [];
if ($student_id) {
    $chat_query = "
        SELECT * FROM teacher_student_chats 
        WHERE (teacher_id = $teacher_id AND student_id = $student_id)
           OR (teacher_id = $student_id AND student_id = $teacher_id)
        ORDER BY sent_at ASC";
    $messages_result = $pdo->query($chat_query);
    while ($row = $messages_result->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $student_id) {
    $message = $pdo->quote(trim($_POST['message']));
    if (!empty($message)) {
        $insert_query = "INSERT INTO teacher_student_chats (teacher_id, student_id, message) VALUES ($teacher_id, $student_id, $message)";
        $pdo->query($insert_query);
        header("Location: teacher_chat.php?student_id=$student_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; height: 100vh; }
        .sidebar { width: 25%; background-color: #f4f4f4; padding: 20px; overflow-y: auto; }
        .chat { flex-grow: 1; display: flex; flex-direction: column; }
        .chat-messages { flex-grow: 1; padding: 20px; overflow-y: auto; background-color: #fafafa; }
        .chat-form { padding: 10px; background-color: #eee; display: flex; }
        .chat-form input[type="text"] { flex-grow: 1; padding: 10px; margin-right: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .chat-form button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .chat-form button:hover { background-color: #0056b3; }
        .message { margin-bottom: 10px; padding: 10px; border-radius: 5px; }
        .message.teacher { background-color: #d1ecf1; text-align: left; }
        .message.student { background-color: #c3e6cb; text-align: right; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Students</h3>
        <ul>
            <?php foreach ($students as $student): ?>
                <li>
                    <a href="teacher_chat.php?student_id=<?php echo $student['id']; ?>">
                        <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat">
        <div class="chat-messages">
            <?php if ($student_id): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo ($msg['teacher_id'] == $teacher_id) ? 'teacher' : 'student'; ?>">
                        <strong>
                            <?php 
                                if ($msg['teacher_id'] == $teacher_id) {
                                    echo 'You';
                                } else {
                                    echo $msg['first_name'] . ' ' . $msg['last_name']; 
                                }
                            ?>:
                            
                        </strong>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <small><?php echo $msg['sent_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Select a student to start chatting.</p>
            <?php endif; ?>
        </div>
        <?php if ($student_id): ?>
            <form class="chat-form" method="POST">
                <input type="text" name="message" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>