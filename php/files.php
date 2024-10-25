<?php
include 'db.php';

try {
    // Fetch file metadata
    $stmt = $pdo->query("SELECT * FROM uploaded_files ORDER BY upload_date DESC");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to retrieve files: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Files</title>
</head>
<body>
    <h1>Uploaded Files</h1>
    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <a href="<?= htmlspecialchars($file['filepath']) ?>" target="_blank">
                    <?= htmlspecialchars($file['filename']) ?>
                </a> (uploaded on <?= $file['upload_date'] ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
