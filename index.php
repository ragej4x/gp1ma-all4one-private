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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">

</head>
<body>
<div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v21.0"></script>

    <div class="container">
        <div class="left">
            <?php
            $profile_pic = !empty($user['profile_pic']) && file_exists("php/uploads/" . $user['profile_pic']) 
                           ? "php/uploads/" . htmlspecialchars($user['profile_pic']) 
                           : "php/uploads/default-profile.png";

            echo "<a href='php/profile.php'><img class='profile' src='$profile_pic' alt='Profile Picture'></a>";
            ?>

            <h2 class="name"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></h2>
            
        </div>

        <div class="middle">
            <h2>Public Announcement</h2>
            <div class="feed-container">
                <div class="fb-page" 
                     data-href="https://www.facebook.com/JonvicRemullaJr" 
                     data-tabs="timeline" 
                     data-small-header="true" 
                     data-adapt-container-width="false" 
                     data-hide-cover="false" 
                     data-width="1000px"
                     data-height="800px"
                     data-show-facepile="true">
                    <blockquote cite="https://www.facebook.com/JonvicRemullaJr" class="fb-xfbml-parse-ignore">
                        <a href="https://www.facebook.com/JonvicRemullaJr">Jonvic Remulla</a>
                    </blockquote>
                </div>
            </div>

            
            <div class="feed-container">
                <div class="fb-page" 
                     data-href="https://www.facebook.com/thephoenixadvisory" 
                     data-tabs="timeline"
                     data-width="1000px"
                     data-height="800px"
                     data-small-header="true" 
                     data-adapt-container-width="false" 
                     data-hide-cover="false" 
                     data-show-facepile="true">
                    <blockquote cite="https://www.facebook.com/thephoenixadvisory" class="fb-xfbml-parse-ignore">
                        <a href="https://www.facebook.com/thephoenixadvisory">The Phoenix Advisory</a>
                    </blockquote>
                </div>
            </div>
            
        </div>

        <div class="right">
            <div class="selections">
                <div class="ico-label">

                    <div class="book-cont" ><a href= "php/view_modules.php"> <img class="icons" id="book" src="icons/book.png" alt="Error Unable to load asset"><h3 id="a1"  class="ico-text" >Lessons</h3></a></div>

                    <div class="group-cont"><a href= "php/index.php"> <img class="icons" id="group" src="icons/group.png" alt="Error Unable to load asset"> <h3 id="b2" class="ico-text">Chats</h3></a></div>

                    <div class="professor-cont" ><a href= "php/index.php"><img class="icons"id="professor" src="icons/professor.png" alt="Error Unable to load asset"><br><h3 id="c3" class="ico-text">Teachers</h3></a></div>

                    <div class="tasks-cont"><a href="php/view_assignment.php"><img class="icons" id="tasks" src="icons/tasks.png" alt="Error Unable to load asset"><br><h3 id="d4" class="ico-text">Assignment</h3></div>
                    <div class="files-cont" ><a href="php/cloud-flm.php"> <img class="icons" id="files" src="icons/folder.png" alt="Error Unable to load asset"><br><h3 id="e5" class="ico-text">My Files</h3></a></div>
                    
                      
                </div>
                
                

        </div>


    </div>

</body>
</html>