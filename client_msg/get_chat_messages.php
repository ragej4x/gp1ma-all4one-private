<?php
session_start();
require 'db.php';  // Include your database connection

// Check if student is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    // Return error as JSON
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$student_id = $_SESSION['id'];  // Get the logged-in student ID
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;  // Get the selected teacher's ID

if ($teacher_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Teacher ID is missing']);
    exit;
}

// Fetch messages between the student and the teacher
try {
    $stmt = $pdo->prepare("
        SELECT * FROM client_msg
        WHERE 
            (sender_id = ? AND sender_type = 'user' AND receiver_id = ? AND receiver_type = 'teacher')
            OR
            (sender_id = ? AND sender_type = 'teacher' AND receiver_id = ? AND receiver_type = 'user')
        ORDER BY created_at ASC
    ");
    $stmt->execute([$student_id, $teacher_id, $teacher_id, $student_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'messages' => $messages]);
} catch (Exception $e) {
    // Return error as JSON if there is a database issue
    echo json_encode(['status' => 'error', 'message' => 'Error fetching messages', 'error' => $e->getMessage()]);
}
?>
