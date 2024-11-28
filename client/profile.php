<?php
// Include database connection
include '../php/db.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: auth.php');
    exit;
}


// Fetch profile information
$teacherId = $_SESSION['teacher_id'];
$teacher = $pdo->query("SELECT * FROM teachers WHERE id = $teacherId")->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $profilePicture = $_FILES['profile_picture']['name'];

    // Validate password confirmation
    if ($password && $password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Process password update only if new password is provided
        $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : $teacher['password'];
        
        // Process profile picture upload if a new picture is provided
        if ($profilePicture) {
            $targetDir = "profile_picture/";
            $targetFile = $targetDir . basename($profilePicture);
            
            // Check if the upload was successful
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicturePath = basename($profilePicture);  // Save only the filename
            } else {
                echo "<script>alert('Failed to upload profile picture.');</script>";
                $profilePicturePath = $teacher['profile_picture'];
            }
        } else {
            $profilePicturePath = $teacher['profile_picture'];
        }

        // Update database
        $stmt = $pdo->prepare("UPDATE teachers SET first_name = ?, last_name = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $profilePicturePath, $teacherId]);
        //echo "<script>alert('Profile updated successfully!'); window.location.href='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <!-- Left Panel: Profile -->
    <aside class="profile-panel">
        <img src="uploads/profile_pics/<?php echo htmlspecialchars($teacher['profile_picture']); ?>" alt="Profile Picture">
        <h3><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h3>
        <p>Email: <?php echo htmlspecialchars($teacher['email']); ?></p>
        <button onclick="document.getElementById('editProfileTab').style.display = 'block'">Edit Profile</button>
        <button onclick="location.href='logout.php'">Logout</button>
    </aside>

    <!-- Main Content: Edit Profile -->
    <main>
        <div id="editProfileTab">
            <h2>Edit Profile</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_profile">
                <input type="text" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                <input type="text" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                <input type="password" name="password" placeholder="New Password">
                <input type="password" name="confirm_password" placeholder="Confirm Password">
                <input type="file" name="profile_picture" accept="image/*">
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
