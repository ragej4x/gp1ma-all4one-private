<?php
include 'db.php';

$stmt = $pdo->prepare("SELECT * FROM uploaded_files ORDER BY is_folder DESC, uploaded_at DESC");
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management</title>
    <link rel="stylesheet" href="style/cloud-flm-style.css">
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">

</head>
<body>

<div class="container">
    <div class="side-panel">
        <h2>File Management</h2>
        <input type="file" id="fileInput" style="display: none;" onchange="uploadFile()" />
        <button onclick="document.getElementById('fileInput').click();" class="side-option">Upload File</button>
        <button onclick="createFolder()" class="side-option">Create Folder</button>
        <button onclick="deleteSelectedFiles()" class="side-option">Delete Selected</button>
        <button id="backButton" onclick="goBack()" style="display: none;">Back</button>
        <a id="return" href="../index.php">Return</a>    
    </div>

    <div class="main-content">
        <div class="file-container" id="file-container">
            <?php foreach ($files as $file): ?>
                <div class="file-item" data-id="<?= $file['id'] ?>" onclick="selectFile(<?= $file['id'] ?>)">
                    <?php if ($file['is_folder']): ?>
                        <div class="icon">📁</div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($file['filepath'], ENT_QUOTES) ?>" class="file-icon" alt="<?= htmlspecialchars($file['filename'], ENT_QUOTES) ?>" />
                    <?php endif; ?>
                    <p><?= htmlspecialchars($file['filename'], ENT_QUOTES) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="javascript/file-script.js"></script>
