<?php
session_start();
include '../php/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Unauthorized");
}

$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;

if ($teacher_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Teacher ID is required']);
    exit;
}

$student_id = $_SESSION['id']; 

$stmt = $pdo->prepare("SELECT * FROM client_msg 
                        WHERE (sender_id = ? AND receiver_id = ? AND sender_type = 'user' AND receiver_type = 'teacher')
                        OR (sender_id = ? AND receiver_id = ? AND sender_type = 'teacher' AND receiver_type = 'user') 
                        ORDER BY created_at ASC");
$stmt->execute([$student_id, $teacher_id, $teacher_id, $student_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
