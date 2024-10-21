<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio']; 

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, bio = ?, password = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $username, $email, $bio, $password, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, bio = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $username, $email, $bio, $user_id]);
    }

    $_SESSION['username'] = $username;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$_FILES["profile_pic"]["name"], $user_id]);
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
    <title>Profile</title>
    <link rel="stylesheet" href="style/profile-style.css">
</head>
<body>

    <div class="box-body">
        <div class="sidebar">

            <?php
            // Set profile picture path with fallback
            $profile_pic = !empty($user['profile_pic']) && file_exists("uploads/" . $user['profile_pic']) 
            ? "uploads/" . htmlspecialchars($user['profile_pic']) 
            : "uploads/default-profile.png"; // Fallback image if profile pic is missing

            // Display profile picture
            echo "<img class='profile-pic' src='$profile_pic' alt='Profile Picture'>";
            ?>

            <div><?php echo '<h2 class="name">' . htmlspecialchars($user['first_name']) . str_repeat("&nbsp;", 1) . htmlspecialchars($user['last_name']) . '</h2>' ?></div>

            <a href="../index.php"><h4 id="return">Return</h4></a>
            <a href="logout.php"><h4 id="logout">Logout</h4></a>

        </div>

        <form method="POST" action="" enctype="multipart/form-data" id="profile-form">
            <br><br><br>
            <label for="first_name">First Name:</label><label for="last_name" id="lname">Last Name:</label>
            <input type="text" placeholder="First Name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
            <input type="text" placeholder="Last Name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
            <label for="username">Username:</label><label id="mail" for="email">Email:</label>

            <input type="text" placeholder="Username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            <br>
            <label for="bio">Bio:</label><br>
            <textarea type="text" rows="4" cols="60" name="bio"><?php echo htmlspecialchars(isset($user['bio']) ? $user['bio'] : ''); ?></textarea>
            <br>
            <br>

            <label for="password">Password:</label><label for="password" id="p2">Confirm Password:</label><br>

            <input type="password" name="password" id="pass"> <input type="password" id="confirm-pass">
            <br>
            <label for="file">Upload Profile Picture:</label><br><br>
            <input type="file" name="profile_pic" value="Upload Profile">
            <button class="btn"  type="submit">Update Profile</button>
        </form>
    </div>

<script src="javascript/confirmation.js"></script>

</body>
</html>




