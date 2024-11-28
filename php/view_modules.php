<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}


$subjects = $pdo->query("SELECT DISTINCT subject FROM modules")->fetchAll(PDO::FETCH_ASSOC);

$selectedSubject = isset($_GET['subject']) ? $_GET['subject'] : '';
$modules = [];
if ($selectedSubject) {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE subject = ?");
    $stmt->execute([$selectedSubject]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Modules</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            background-color: #F1F6F9;
        }
        .sidebar {
            width: 250px;
            background-color: #394867;
            color: #ffffff;
            padding: 15px;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            margin-top: 0;
        }
        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background-color: #212A3E;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
        }
        .module {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .module h3 {
            margin: 0;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Subjects</h2>
    <?php foreach ($subjects as $subject): ?>
        <a href="?subject=<?php echo urlencode($subject['subject']); ?>"><?php echo htmlspecialchars($subject['subject']); ?></a>
    <?php endforeach; ?>
</div>

<div class="content">
    <h1>Modules for <?php echo htmlspecialchars($selectedSubject ?: 'All Subjects'); ?></h1>

    <?php if ($modules): ?>
        <?php foreach ($modules as $module): ?>
            <div class="module">
                <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($module['description']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($module['date']); ?></p>
                <?php if ($module['file_attachment']): ?>
                    <p><strong>File:</strong> <a href="../client/uploads/modules/<?php echo htmlspecialchars($module['file_attachment']); ?>" target="_blank">Download</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No modules available for this subject.</p>
    <?php endif; ?>
</div>

</body>
</html>
