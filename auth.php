<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'php/db.php';

$error = ""; 
$register_error = ""; 

if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // hash

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$first_name, $last_name, $username, $email, $password])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = "Registration failed. Please try again.";
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid Email or Password";
        }
    } else {
        $error = "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=5.0">
    <title>GP1MA</title>
    <link rel="stylesheet" href="style/auth-style.css">
    <link rel="icon" type="image/x-icon" href="icons/favicon.png">
</head>

<body>
<div class="logo" style=" position:absolute; background-color:#F1F6F9; height:100vh; width:50%;" ><img style="width:600px; height:600px; margin-left:15%; margin-top:10%;" id="logo" src="icons/logo.png" alt="logo">


</div>
<div class="container">

    <section class="forms-section">
        <h1 class="section-title"></h1>
        <div class="forms">
            <div class="form-wrapper is-active">
                <button type="button" id="login-swt" class="switcher switcher-login">Login<span class="underline"></span></button>
                <form class="form form-login" method="POST">
                    <fieldset>
                        <legend>Please, enter your email and password for login.</legend>
                        <div class="input-block">
                            <label for="login-email">E-mail</label>
                            <input id="login-email" name="email" type="email" required>
                        </div>
                        <div class="input-block">
                            <label for="login-password">Password</label>
                            <input id="login-password" name="password" type="password" required>
                        </div>
                        <?php if ($error): ?>
                            <div class="input-block" style="color: red; margin-top: 10px; text-align:center;"><?php echo $error; ?></div>
                        <?php endif; ?>
                    </fieldset>
                    <button name="login" type="submit" class="btn-login">Login</button>
                </form>
            </div>
            <div class="form-wrapper">
                <button type="button" id="reg-swt" class="switcher switcher-signup">Sign Up<span class="underline"></span></button>
                <form id="reg-form" class="form form-signup" method="POST" onsubmit="return validatePasswords();">
                    <fieldset>
                        <legend>Please, enter your email, password and password confirmation for sign up.</legend>
                        <div class="input-block">
                            <label for="signup-fname">First Name</label>
                            <label for="signup-lname">Last Name</label>
                            <input id="signup-fname" name="first_name" type="text" required>
                            <input id="signup-lname" name="last_name" type="text" required>
                        </div>
                        <div class="input-block">
                            <label for="signup-email">E-mail</label>
                            <input id="signup-email" name="email" type="email" required>
                        </div>
                        <div class="input-block">
                            <label for="signup-username">User Name</label>
                            <input id="signup-username" name="username" type="text" required>
                        </div>
                        <div class="input-block">
                            <label for="signup-password">Password</label>
                            <label for="signup-password-confirm">Confirm Password</label>
                            <input id="signup-password" name="password" type="password" required>
                            <input id="signup-password-confirm" name="confirm_password" type="password" required>
                            <span id="password-error" class="error" style="display: none;">Passwords do not match!</span>
                        </div>
                    </fieldset>
                    <button name="register" type="submit" class="btn-signup">Continue</button>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isMobile = /Mobi|Android/i.test(navigator.userAgent) || window.innerWidth <= 768;
    if (isMobile) {
        const meta = document.createElement('meta');
        meta.name = "viewport";
        meta.content = "width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no";
        document.head.appendChild(meta);
    }
});
//yawa na mobile
function removeClassOnMobile() {
    const signupElement = document.querySelector('.switcher-signup');
    const loginElement = document.querySelector('.switcher-login'); 

    if (window.innerWidth <= 768) {
        if (signupElement) {
            signupElement.classList.remove('switcher-signup');
        }
        if (loginElement) {
            loginElement.classList.remove('switcher-login'); 
        }
    } else {
        if (signupElement) {
            signupElement.classList.add('switcher-signup'); 
        }
        if (loginElement) {
            loginElement.classList.add('switcher-login'); 
        }
    }
}
window.addEventListener('load', removeClassOnMobile);
window.addEventListener('resize', removeClassOnMobile);
</script>

<script src="javascript/auth-switcher.js"></script>
<script src="javascript/scaler.js"></script>
<script src="javascript/confirmation.js"></script>

</body>
</html>
