<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$subjects = $pdo->query("SELECT DISTINCT subject FROM assignments")->fetchAll(PDO::FETCH_ASSOC);

$selectedSubject = isset($_GET['subject']) ? $_GET['subject'] : '';
$assignments = [];
if ($selectedSubject) {
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE subject = ?");
    $stmt->execute([$selectedSubject]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments</title>
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">

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
            margin-left: 290px;
            padding: 20px;
            flex-grow: 1;
        }
        .assignment {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .assignment h3 {
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


    <div class="nav" style="position:absolute; margin-bottom: 30px; bottom:0;"><a href="../index.php">Return</a></div>
</div>

<div class="content">
    <h1>Assignments for <?php echo htmlspecialchars($selectedSubject ?: 'All Subjects'); ?></h1>

    <?php if ($assignments): ?>
        <?php foreach ($assignments as $assignment): ?>
            <div class="assignment">
                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                <p><strong>Deadline:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($assignment['deadline']))); ?></p>
                <?php if ($assignment['file_attachment']): ?>
                    <p><strong>File:</strong> <a href="../client/uploads/assignments/<?php echo htmlspecialchars($assignment['file_attachment']); ?>" target="_blank">Download</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No assignments available for this subject.</p>
    <?php endif; ?>
</div>

</body>
</html>
