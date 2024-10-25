<?php
include 'db.php';

$folderId = isset($_GET['folderId']) ? intval($_GET['folderId']) : null;

if ($folderId !== null) {
    // Fetch files and folders for the specified folder
    $stmt = $pdo->prepare("SELECT * FROM uploaded_files WHERE parent_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$folderId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the result as JSON
    echo json_encode([
        'success' => true,
        'files' => $files,
    ]);
} else {
    // If folderId is not provided, return an error
    echo json_encode([
        'success' => false,
        'message' => 'Folder ID is required.',
    ]);
}
?>
