<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'php/db.php'; 

if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

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

// Handle login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid Username or Password" ;
    }


    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } 
            
        
    }
}
?>



<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style/style.css">

    </head>
    <body>
        <div class="center">
        <div class="box-body">

                    
                
            <div id="login-page">
                <br><br><br>
                <br><br><br>
                <title>Login</title>
                <form action="" method="POST">

                    <?php if (!empty($error)): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <input required class='input' name="email" type="login" placeholder="Email"><br>
                    <input required class='input' name="password" type="password" placeholder="password"><br>
                    <input class="btn" name="login" type="submit" value="Login">
                    <input class="btn" type="button" value="Register" onclick="showReg()">
                    
                </form>
                
            </div>

            <div id="reg-page">
                <title>Regster</title>
                <div>
                    <form id="reg-form" action="" method="POST">
                    <br><br>
                    <br>
                        <input required class='input' type="text" name="first_name" placeholder="First Name"><br>
                        <input required class='input'  type="text" name="last_name" placeholder="Last Name"><br>
                        <input required class='input' type="text" name="username" placeholder="Username"><br>
                        <input required class='input' type="email" name="email" placeholder="Email" id="mail-1"><br>
                        <input required class='input' type="password" name="password" placeholder="Password" id="pass-1"><br>
                        <input required class='input' type="password" placeholder="Confirm Password" id="con-pass"><br>
                        <input class='btn' type="submit" name="register" value="Register">
                        <input class='btn' type="button" value="Login Instead" onclick="showLp()">
                    </form>

                </div>
            </div>

        </div>
        </div>
        

    </body>
    <script src="actions.js"></script>
    <script src="form.js"></script>

</html>