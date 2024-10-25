<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $newName = $_POST['newName'];

    $stmt = $pdo->prepare("UPDATE uploaded_files SET filename = :newName WHERE id = :id");
    $stmt->bindParam(':newName', $newName);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $success = $stmt->execute();

    echo json_encode(['success' => $success]);
}
?>
