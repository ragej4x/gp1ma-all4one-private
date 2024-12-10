<?php
session_start();
include '../../php/db.php'; // Include your database connection file

// Check if teacher is logged in
// Check if role is set and is 'teacher'
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    die("Unauthorized");
}


$teacher_id = $_SESSION['id']; // Get teacher ID from session

// Fetch students for this teacher
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ?");
$stmt->execute([$teacher_id]); // Don't allow teacher to chat with themselves
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        /* Sidebar Style */
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
        /* Chat Area */
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
                    <?= $student['first_name'] . " " . $student['last_name']; ?>
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

        // Start chat when a student is clicked
        function startChat(studentId) {
            selectedStudentId = studentId;
            document.getElementById("messages").innerHTML = ""; // Clear chat messages
            loadMessages(); // Load previous chat messages if any
        }

        // Load chat messages from the server (AJAX)
        function loadMessages() {
    if (selectedStudentId === null) return;

    fetch(`get_chat_messages.php?student_id=${selectedStudentId}`)
        .then(response => response.json())
        .then(data => {
            const messagesDiv = document.getElementById("messages");
            messagesDiv.innerHTML = ''; // Clear previous messages

            // Check if we have messages
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

            // Scroll to the bottom to show the latest message
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(error => console.log("Error loading messages:", error));
}


        // Send a message to the selected student
        function sendMessage() {
            const message = document.getElementById("message").value;
            if (!message || selectedStudentId === null) return;

            fetch('send_message.php', {
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
                document.getElementById("message").value = ""; // Clear message input
                loadMessages(); // Reload messages
            })
            .catch(error => console.log(error));
        }
    </script>
</body>
</html>
