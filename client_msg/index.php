<?php
session_start();
include '../php/db.php'; // Include your database connection file

// Check if student is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Unauthorized");
}

$student_id = $_SESSION['id']; // Get student ID from session

// Fetch all teachers for the student
$stmt = $pdo->prepare("SELECT * FROM teachers");
$stmt->execute();
$teachers = $stmt->fetchAll();

// Handle message loading and sending via AJAX
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'load_messages' && isset($_GET['teacher_id'])) {
        $teacher_id = $_GET['teacher_id'];

        // Fetch messages between student and selected teacher
        $stmt = $pdo->prepare("SELECT * FROM client_msg 
            WHERE (sender_id = :student_id AND receiver_id = :teacher_id AND sender_type = 'user' AND receiver_type = 'teacher') 
            OR (sender_id = :teacher_id AND receiver_id = :student_id AND sender_type = 'teacher' AND receiver_type = 'user') 
            ORDER BY created_at ASC");
        
        // Bind the parameters to the statement
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    
        // Execute the query
        $stmt->execute();
    
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Debugging: Log or output messages to check if it's being fetched correctly
        error_log(print_r($messages, true)); // This will log the messages to the PHP error log
    
        if ($messages) {
            echo json_encode(['status' => 'success', 'messages' => $messages]);
        } else {
            echo json_encode(['status' => 'success', 'messages' => []]); // Ensure empty array if no messages
        }
        exit;
    } elseif ($action == 'send_message') {
        $data = $_POST;

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
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        /* Sidebar Style */
        #sidebar {
            width: 250px;
            float: left;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .teacher-info {
            padding: 10px;
            background-color: #e2e2e2;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .teacher-info:hover {
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
        #messages {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <h3>Teachers</h3>
        <?php if ($teachers): ?>
            <?php foreach ($teachers as $teacher): ?>
                <div class="teacher-info" onclick="loadMessages(<?= $teacher['id']; ?>)">
                    <strong><?= htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']); ?></strong>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No teachers found.</p>
        <?php endif; ?>
    </div>

    <div id="chat-box">
        <h3>Chat with your teacher</h3>
        <div id="messages">
            <!-- Messages will be dynamically loaded here -->
        </div>

        <form id="message-form" style="display: none;">
            <input type="hidden" name="sender_id" id="sender_id">
            <input type="hidden" name="receiver_id" id="receiver_id">
            <input type="hidden" name="sender_type" value="user">
            <input type="hidden" name="receiver_type" value="teacher">
            <textarea name="message" id="message" placeholder="Type your message here..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        // Initialize variables
        const messagesDiv = document.getElementById('messages');
        const messageForm = document.getElementById('message-form');
        const senderIdInput = document.getElementById('sender_id');
        const receiverIdInput = document.getElementById('receiver_id');
        
        // Function to load messages dynamically for a selected teacher
        function loadMessages(teacher_id) {
            // Set the selected teacher's ID to the receiver field
            receiverIdInput.value = teacher_id;
            
            // Show the message form
            messageForm.style.display = 'block';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', '?action=load_messages&teacher_id=' + teacher_id, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    messagesDiv.innerHTML = ''; // Clear previous messages

                    if (data.status === 'success') {
                        if (data.messages && data.messages.length > 0) {
                            data.messages.forEach(function(message) {
                                const sender = message.sender_type === 'teacher' ? 'Teacher' : 'Student';
                                const messageElement = document.createElement('div');
                                messageElement.innerHTML = `<strong>${sender}:</strong> ${message.message}`;
                                messagesDiv.appendChild(messageElement);
                            });
                        } else {
                            messagesDiv.innerHTML = '<div>No messages yet.</div>';
                        }
                        // Scroll to the bottom
                        messagesDiv.scrollTop = messagesDiv.scrollHeight;
                    } else {
                        console.error("Error loading messages:", data.message);
                        messagesDiv.innerHTML = '<div>Error loading messages.</div>';
                    }
                }
            };
            xhr.send();
        }

        // Send message via AJAX
        messageForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(messageForm);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?action=send_message', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        const teacher_id = receiverIdInput.value;
                        loadMessages(teacher_id); // Reload messages after sending
                        messageForm.reset(); // Clear message input
                    } else {
                        alert('Error sending message');
                    }
                }
            };
            xhr.send(formData);
        });
    </script>
</body>
</html>
