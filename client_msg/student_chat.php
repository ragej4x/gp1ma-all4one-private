<?php
include '../php/db.php';
// Start session
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login_student.php');
    exit;
}

// Get the student's ID from the session
$student_id = $_SESSION['user_id'];

// Fetch all teachers to chat with, including their profile pictures
$stmt = $pdo->prepare("SELECT id, first_name, last_name, profile_picture FROM teachers");
$stmt->execute();
$teachers = $stmt->fetchAll();

// Handle chat selection
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : null;

// Fetch the chat messages between the student and the selected teacher
$messages = [];
if ($teacher_id) {
    $chat_query = "
        SELECT tc.*, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, 
               s.first_name AS student_first_name, s.last_name AS student_last_name
        FROM teacher_student_chats tc
        LEFT JOIN teachers t ON t.id = tc.teacher_id
        LEFT JOIN users s ON s.id = tc.student_id
        WHERE (tc.teacher_id = :teacher_id AND tc.student_id = :student_id)
           OR (tc.teacher_id = :student_id AND tc.student_id = :teacher_id)
        ORDER BY tc.sent_at ASC";
    
    $stmt = $pdo->prepare($chat_query);
    $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $teacher_id) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Insert the message into the database
        $insert_query = "INSERT INTO teacher_student_chats (teacher_id, student_id, message) 
                         VALUES (:teacher_id, :student_id, :message)";
                         
        $stmt = $pdo->prepare($insert_query);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->execute();
        
        // Redirect to the same page to display the new message
        header("Location: student_chat.php?teacher_id=$teacher_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Chat</title>
    <style>
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    height: 100vh;
    background-color: #F1F6F9;
    color: #394867;
}
.sidebar {
    width: 280px;
    background-color: #212A3E;
    padding: 20px;
    overflow-y: auto;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Make sure the teacher list stays at the top */
}
.sidebar h3 {
    font-size: 1.4em;
    margin-bottom: 10px; /* Adjust the margin to push the header closer to the list */
    color: #9BA4B5;
}
.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0; /* Remove any extra margin */
}
.sidebar li {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}
.sidebar a {
    color: #fff;
    text-decoration: none;
    font-size: 1.1em;
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 5px;
    transition: background-color 0.3s;
}
.sidebar a:hover {
    background-color: #394867;
}
.sidebar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
}
.chat {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    padding: 30px;
    background-color: #F1F6F9;
}
.chat-messages {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.chat-form {
    padding: 15px;
    background-color: #fff;
    display: flex;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.chat-form input[type="text"] {
    flex-grow: 1;
    padding: 12px;
    margin-right: 15px;
    border: 1px solid #9BA4B5;
    border-radius: 5px;
    font-size: 1em;
}
.chat-form button {
    padding: 12px 20px;
    background-color: #394867;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s;
}
.chat-form button:hover {
    background-color: #212A3E;
}
.message {
    margin-bottom: 15px;
    padding: 12px;

    border-radius: 8px;
    max-width: 100%;
    font-size: 1em;
    line-height: 1.4;
}
.message.teacher {
    background-color: #D1ECF1;
    text-align: left;
}
.message.student {
    background-color: #C3E6CB;
    text-align: right; 
}
.message .sender {
    font-weight: bold;
    margin-bottom: 5px;
}
small {
    display: block;
    margin-top: 5px;
    font-size: 0.8em;
    color: #9BA4B5;
}
.return-button {
    
    padding: 0;
    max-width: 50px;
    background-color: #212A3E;
    color: white;
    text-decoration: none;
    font-size: 1.1em;
    border-radius: 5px;
    text-align: center;
    margin-top: 20px;
    transition: background-color 0.3s;
    cursor: pointer;
    margin-top: 160%;
}
.return-button:hover {
    background-color: #394867;
}

    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Teachers</h3>
        <ul>
            <?php foreach ($teachers as $teacher): ?>
                <li>
                    <a href="student_chat.php?teacher_id=<?php echo $teacher['id']; ?>">
                        <img src="<?php echo (!empty($teacher['profile_picture']) && file_exists("../client/profile_picture/" . $teacher['profile_picture'])) ? '../client/profile_picture/' . $teacher['profile_picture'] : 'uploads/default-profile.png'; ?>" alt="Teacher Profile Picture">
                        <?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="../index.php" class="return-button">Return</a>
    </div>
    <div class="chat">
        <div class="chat-messages">
            <?php if ($teacher_id): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo ($msg['student_id'] == $student_id) ? 'student' : 'teacher'; ?>">
                        <div class="sender">
                            <?php 
                                if ($msg['student_id'] == $student_id) {
                                    echo 'You';
                                } else {
                                    echo $msg['teacher_first_name'] . ' ' . $msg['teacher_last_name'];
                                }
                            ?>
                        </div>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <small><?php echo $msg['sent_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Select a teacher to start chatting.</p>
            <?php endif; ?>
        </div>
        <?php if ($teacher_id): ?>
            <form class="chat-form" method="POST">
                <input type="text" name="message" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
