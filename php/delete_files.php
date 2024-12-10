<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$stmt = $pdo->prepare("DELETE FROM uploaded_files WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>
