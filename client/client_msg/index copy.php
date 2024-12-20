<?php
session_start();
include '../../php/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    die("Unauthorized");
}

$teacher_id = $_SESSION['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ?");
$stmt->execute([$teacher_id]); 
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        #sidebar {
            width: 250px;
            float: left;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .student-list {
            list-style: none;
            padding: 0;
        }
        .student-list li {
            padding: 10px;
            background-color: #e2e2e2;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .student-list li:hover {
            background-color: #d1d1d1;
        }
        #chat-box {
            margin-left: 270px;
            padding: 20px;
            background-color: #fff;
            height: 400px;
            border: 1px solid #ccc;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <h3>Students</h3>
        <ul class="student-list">
            <?php foreach ($students as $student): ?>
                <li onclick="startChat(<?= $student['id']; ?>)">
                    <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="chat-box">
        <h3>Select a student to start chatting</h3>
        <div id="messages"></div>
        <textarea id="message" placeholder="Type your message here..."></textarea>
        <button onclick="sendMessage()">Send</button>
    </div>

    <script>
        let selectedStudentId = null;

        function startChat(studentId) {
            selectedStudentId = studentId;
            document.getElementById("messages").innerHTML = ""; 
            loadMessages(); 
        }

        function loadMessages() {
            if (selectedStudentId === null) return;

            fetch(`?action=load_messages&student_id=${selectedStudentId}`)
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById("messages");
                    messagesDiv.innerHTML = ''; // Clear previous messages

                    if (data.status === "success" && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            const senderName = message.sender_type === 'teacher' ? 'Teacher' : 'Student';
                            messageElement.textContent = `${senderName}: ${message.message}`;
                            messagesDiv.appendChild(messageElement);
                        });
                    } else {
                        messagesDiv.innerHTML = '<div>No messages yet</div>';
                    }

                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                })
                .catch(error => console.log("Error loading messages:", error));
        }

        function sendMessage() {
            const message = document.getElementById("message").value;
            if (!message || selectedStudentId === null) return;

            fetch('?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    sender_id: <?= $teacher_id; ?>,
                    receiver_id: selectedStudentId,
                    sender_type: 'teacher',
                    receiver_type: 'user',
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("message").value = ""; 
                loadMessages();
            })
            .catch(error => console.log(error));
        }
    </script>

    <?php
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action == 'load_messages') {
            $student_id = $_GET['student_id'] ?? null;
            if (!$student_id) {
                echo json_encode(['status' => 'error', 'message' => 'Student ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT * FROM client_msg 
                WHERE (sender_id = ? AND receiver_id = ? AND sender_type = 'teacher' AND receiver_type = 'user') 
                OR (sender_id = ? AND receiver_id = ? AND sender_type = 'user' AND receiver_type = 'teacher') 
                ORDER BY created_at ASC");
            $stmt->execute([$teacher_id, $student_id, $student_id, $teacher_id]);

            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'messages' => $messages]);
        } elseif ($action == 'send_message') {
            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['sender_id'], $data['receiver_id'], $data['sender_type'], $data['receiver_type'], $data['message'])) {
                $stmt = $pdo->prepare("INSERT INTO client_msg (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['sender_id'],
                    $data['sender_type'],
                    $data['receiver_id'],
                    $data['receiver_type'],
                    $data['message']
                ]);

                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid data"]);
            }
        }

        exit; 
    }
    ?>
</body>
</html>
