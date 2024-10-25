<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "uploads/";
    $fileToUpload = $_FILES['fileToUpload'];

    if ($fileToUpload['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload error.']);
        exit;
    }

    $targetFilePath = $targetDir . basename($fileToUpload['name']);
    if (move_uploaded_file($fileToUpload['tmp_name'], $targetFilePath)) {
        $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename, filepath, is_folder) VALUES (?, ?, 0)");
        $stmt->execute([$fileToUpload['name'], $targetFilePath]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
