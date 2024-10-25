<?php
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if folderName is set
    if (isset($_POST['folderName']) && !empty($_POST['folderName'])) {
        $folderName = $_POST['folderName'];

        // Optional: Sanitize folder name
        $folderName = htmlspecialchars($folderName, ENT_QUOTES);

        // Insert folder into the database
        $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename, is_folder, parent_id) VALUES (?, 1, ?)");
        $parentId = isset($_POST['parent_id']) ? $_POST['parent_id'] : null; // Get parent ID if set
        
        // Execute the query and check for success
        if ($stmt->execute([$folderName, $parentId])) {
            echo json_encode(['success' => true, 'message' => 'Folder created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create folder.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Folder name is required.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}