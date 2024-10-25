<?php
session_start();
include 'db.php'; // Make sure to include your database connection here

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Gather user inputs
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Handle password change if needed
    if (!empty($password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, bio = ?, password = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $username, $email, $bio, $hashed_password, $user_id]);
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, bio = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $username, $email, $bio, $user_id]);
    }

    $_SESSION['username'] = $username;

    // Profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([basename($_FILES["profile_pic"]["name"]), $user_id]); // Only save the filename
                header('Location: profile.php'); 
                exit;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style/profile_style.css">
</head>
<body>
    <div class="profile-container">
        <div class="left-panel">
            <?php
            // Profile picture display logic
            $profile_pic = !empty($user['profile_pic']) && file_exists("uploads/" . $user['profile_pic']) 
                ? "uploads/" . htmlspecialchars($user['profile_pic']) 
                : "uploads/default-profile.png"; // Fallback image
            ?>
            <div class="profile-pic">
                <img src="<?= $profile_pic ?>" alt="Profile Picture">
            </div>
            <h2><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></h2>
            <div class="button-container">
                <a class="side-pan-button" href="../index.php">Return</a>
                <a class="side-pan-button" href="logout.php" >Logout</a>
            </div>
        </div>

        <div class="right-panel">
            <form method="POST" action="" enctype="multipart/form-data" id="profile-form">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" required>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" required>

                <label for="username">Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

                <label for="bio">Bio:</label>
                <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio']); ?></textarea>

                <label for="password">Password:</label>
                <input type="password" name="password" id="pass">

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_pass">

                <div class="error-main">
                    <?php if (!empty($error)): ?>
                    <div class="error"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                </div>

                <div class="file-upload-container">
                    <div class="button-group">
                        <input type="file" id="file-upload" name="profile_pic" class="file-input" />
                        <button class="file-button" type="button" onclick="document.getElementById('file-upload').click();">
                            Upload File
                        </button>
                        
                        <button type="submit" class="update-button">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
