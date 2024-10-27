<?php
// Include database connection
include '../php/db.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit;
}

// Handle assignment posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_assignment') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    // Combine date and time for deadline
    $deadlineDate = $_POST['deadline_date'];
    $deadlineHour = $_POST['deadline_hour'];
    $deadlineMinute = $_POST['deadline_minute'];
    $deadlineAMPM = $_POST['deadline_ampm'];
    
    // Format the deadline time into a 24-hour format
    $formattedTime = ($deadlineAMPM === 'PM' && $deadlineHour != 12) ? $deadlineHour + 12 : $deadlineHour;
    if ($deadlineAMPM === 'AM' && $deadlineHour == 12) {
        $formattedTime = 0; // Midnight case
    }
    $deadline = date("Y-m-d H:i:s", strtotime("$deadlineDate $formattedTime:$deadlineMinute"));

    $fileAttachment = $_FILES['assignment_file']['name'];

    // Handle file upload
    if ($fileAttachment) {
        $targetDir = "uploads/assignments/";
        $targetFile = $targetDir . basename($fileAttachment);
        move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetFile);
    }

    // Insert the assignment into the database
    $stmt = $pdo->prepare("INSERT INTO assignments (title, description, deadline, file_attachment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $deadline, $fileAttachment]);
    echo "<script>alert('Assignment posted successfully!'); window.location.href='index.php';</script>";
}

// Handle module posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_module') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $fileAttachment = $_FILES['file_attachment']['name'];

    // Handle file upload
    if ($fileAttachment) {
        $targetDir = "uploads/modules/";
        $targetFile = $targetDir . basename($fileAttachment);
        move_uploaded_file($_FILES['file_attachment']['tmp_name'], $targetFile);
    }

    // Insert the module into the database
    $stmt = $pdo->prepare("INSERT INTO modules (title, description, date, file_attachment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $date, $fileAttachment]);
    echo "<script>alert('Module posted successfully!'); window.location.href='index.php';</script>";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $profilePicture = $_FILES['profile_picture']['name'];

    if ($profilePicture) {
        $targetDir = "uploads/profile_pics/";
        $targetFile = $targetDir . basename($profilePicture);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile);
    }

    $teacherId = $_SESSION['teacher_id']; // Get the actual teacher ID from session
    $stmt = $pdo->prepare("UPDATE teachers SET first_name = ?, last_name = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$name, $name, $email, $password, $profilePicture, $teacherId]);
    echo "<script>alert('Profile updated successfully!'); window.location.href='index.php';</script>";
}

// Fetch assignments and modules for student view
$assignments = $pdo->query("SELECT * FROM assignments")->fetchAll(PDO::FETCH_ASSOC);
$modules = $pdo->query("SELECT * FROM modules")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher-Student Application</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>

<h1>Welcome, <?php echo $_SESSION['teacher_name']; ?>!</h1>

<!-- Post Assignment Form -->
<h2>Post Assignment</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="post_assignment">
    <input type="text" name="title" placeholder="Assignment Title" required />
    <textarea name="description" placeholder="Description" required></textarea>
    
    <!-- Deadline Date -->
    <input type="date" name="deadline_date" required />
    
    <!-- Hour Selection -->
    <select name="deadline_hour" required>
        <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>

    <!-- Minute Selection -->
    <select name="deadline_minute" required>
        <?php for ($i = 0; $i < 60; $i += 5): ?>
            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
        <?php endfor; ?>
    </select>
    
    <!-- AM/PM Selection -->
    <select name="deadline_ampm" required>
        <option value="AM">AM</option>
        <option value="PM">PM</option>
    </select>
    
    <input type="file" name="assignment_file" accept=".pdf, .jpg, .png" />
    <button type="submit">Post Assignment</button>
</form>

<!-- Post Module Form -->
<h2>Post Module</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="post_module">
    <input type="text" name="title" placeholder="Module Title" required />
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="date" name="date" placeholder="Date" required />
    <input type="file" name="file_attachment" accept=".pdf, .jpg, .png" />
    <button type="submit">Post Module</button>
</form>

<!-- Edit Profile Form -->
<h2>Edit Profile</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit_profile">
    <input type="text" name="name" placeholder="Your Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="New Password" />
    <input type="file" name="profile_picture" accept="image/*" />
    <button type="submit">Update Profile</button>
</form>


</body>
</html>
