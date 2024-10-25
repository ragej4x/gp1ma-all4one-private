<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user['first_name'] = !empty($user['first_name']) ? $user['first_name'] : 'Unknown';
$user['last_name'] = !empty($user['last_name']) ? $user['last_name'] : 'User';
$user['profile_picture'] = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default-profile.png';



if (!$user) {
    echo "Error: User data not found.";
    exit;
}


$profile_pic = "php/uploads/" . htmlspecialchars($user['profile_picture']);

if (!file_exists($profile_pic)) {
    $profile_pic = "php/uploads/default-profile.png";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Layout</title>
    <link rel="stylesheet" href="style/index.css">

</head>

<body>
    <div class="container">

        <div class="left-panel" onclick="expandProfile()">
            <div class="profile-container">

                <a href="php/profile.php">
                    <img class="profile-pic" src="<?php echo $profile_pic; ?>" alt="Profile Picture">
                </a>
                <h2 class="profile-name"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></h2>
            </div>
        </div>

        <div class="feed-section">
            <div class="feed">
                <h2>Feed</h2>
            </div>
        </div>

        <div class="right-panel" onclick="expandRightPanel()">
            <div class="icon" id="book">
                <img src="icons/book.png" alt="Lessons Icon" class="icons">
                <h3 class="icon-label">Lessons</h3>
            </div>

            <div class="icon" id="chat">
                <a href="#" onclick="handleChatClick(event);">
                    <img src="icons/group.png" alt="Chats Icon" class="icons">
                    <h3 class="icon-label">Chats</h3>
                </a>
            </div>


            <div class="icon" id="professor">

                <img src="icons/professor.png" alt="Teachers Icon" class="icons">
                <h3 class="icon-label">Teachers</h3>
            </div>


            <div class="icon" id="tasks">
                <img src="icons/tasks.png" alt="Assignments Icon" class="icons">
                <h3 class="icon-label">Assignments</h3>
            </div>


            <div class="icon" id="files">
                <img src="icons/folder.png" alt="My Files Icon" class="icons">
                <h3 class="icon-label">My Files</h3>
            </div>


        </div>
    </div>

    <script>
        // check lick
        document.addEventListener('DOMContentLoaded', function () {
            const leftPanel = document.querySelector('.left-panel');
            const rightPanel = document.querySelector('.right-panel');

            window.expandProfile = function () {
                leftPanel.classList.toggle('expanded');
            };

            window.expandRightPanel = function () {
                rightPanel.classList.toggle('expanded');
            };

            window.handleChatClick = function (event) {
                event.preventDefault(); 
                event.stopPropagation(); 

                if (rightPanel.classList.contains('expanded')) {
                    window.location.href = 'php/index.php';
                } else {

                    rightPanel.classList.toggle('expanded');
                }
            };
        });
    </script>
</body>
</html>
