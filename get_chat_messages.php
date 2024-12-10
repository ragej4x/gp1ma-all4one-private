<?php
session_start();
include '../php/db.php'; // Include your database connection file

// Check if student is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Unauthorized");
}

// Get the teacher ID from the request
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;

if ($teacher_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Teacher ID is required']);
    exit;
}

$student_id = $_SESSION['id']; // Get student ID from session

// Fetch messages between the student and the selected teacher
$stmt = $pdo->prepare("SELECT * FROM client_msg 
                        WHERE (sender_id = ? AND receiver_id = ? AND sender_type = 'user' AND receiver_type = 'teacher')
                        OR (sender_id = ? AND receiver_id = ? AND sender_type = 'teacher' AND receiver_type = 'user') 
                        ORDER BY created_at ASC");
$stmt->execute([$student_id, $teacher_id, $teacher_id, $student_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the messages as JSON
echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
