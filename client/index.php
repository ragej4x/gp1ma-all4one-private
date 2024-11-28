<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_assignment') {
    $subject = $_POST['subject'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadlineDate = $_POST['deadline_date'];
    $deadlineHour = $_POST['deadline_hour'];
    $deadlineMinute = $_POST['deadline_minute'];
    $deadlineAMPM = $_POST['deadline_ampm'];

    $formattedTime = ($deadlineAMPM === 'PM' && $deadlineHour != 12) ? $deadlineHour + 12 : $deadlineHour;
    if ($deadlineAMPM === 'AM' && $deadlineHour == 12) $formattedTime = 0;
    $deadline = date("Y-m-d H:i:s", strtotime("$deadlineDate $formattedTime:$deadlineMinute"));

    $fileAttachment = $_FILES['assignment_file']['name'];
    if ($fileAttachment) {
        $targetDir = "uploads/assignments/";
        $targetFile = $targetDir . basename($fileAttachment);
        move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetFile);
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (subject, title, description, deadline, file_attachment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$subject, $title, $description, $deadline, $fileAttachment]);
    echo "<script>alert('Assignment posted successfully!'); window.location.href='index.php';</script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_module') {
    $subject = $_POST['subject'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $fileAttachment = $_FILES['file_attachment']['name'];

    if ($fileAttachment) {
        $targetDir = "uploads/modules/";
        $targetFile = $targetDir . basename($fileAttachment);
        move_uploaded_file($_FILES['file_attachment']['tmp_name'], $targetFile);
    }

    $stmt = $pdo->prepare("INSERT INTO modules (subject, title, description, date, file_attachment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$subject, $title, $description, $date, $fileAttachment]);
    echo "<script>alert('Module posted successfully!'); window.location.href='index.php';</script>";
}

$teacherId = $_SESSION['teacher_id'];
$teacher = $pdo->query("SELECT * FROM teachers WHERE id = $teacherId")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="container">
    <div class="profile-info">
        <img src="uploads/profile_pics/<?php echo $teacher['profile_picture']; ?>" alt="Profile Picture" class="profile-pic">
        <div>
            <h1>Welcome, <?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?>!</h1>
            <p>Email: <?php echo $teacher['email']; ?></p>
        </div>
    </div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
</div>

<div class="container">
    <h2>Post Assignment</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="post_assignment">
        <input type="text" name="subject" placeholder="Subject" required />
        <input type="text" name="title" placeholder="Assignment Title" required />
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="date" name="deadline_date" required />
        <div class="select-time">
            <select name="deadline_hour" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <select name="deadline_minute" required>
                <?php for ($i = 0; $i < 60; $i += 5): ?>
                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                <?php endfor; ?>
            </select>
            <select name="deadline_ampm" required>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </div>
        <input type="file" name="assignment_file" accept=".pdf, .jpg, .png" />
        <button type="submit">Post Assignment</button>
    </form>
</div>

<div class="container">
    <h2>Post Module</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="post_module">
        <input type="text" name="subject" placeholder="Subject" required />
        <input type="text" name="title" placeholder="Module Title" required />
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="date" name="date" required />
        <input type="file" name="file_attachment" accept=".pdf, .jpg, .png" />
        <button type="submit">Post Module</button>
    </form>
</div>

</body>
</html>
