<?php
// Include database connection
include '../php/db.php';
session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to find the teacher
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
    $stmt->execute([$email]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher && password_verify($password, $teacher['password'])) {
        // Login successful, set session variables
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
        header('Location: index.php'); // Redirect to the main content page
        exit;
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['register_email'];
    $password = $_POST['register_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die("<script>alert('Passwords do not match.');</script>");
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the teacher into the database
    $stmt = $pdo->prepare("INSERT INTO teachers (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword])) {
        echo "<script>alert('Account created successfully! You can now log in.');</script>";
    } else {
        echo "<script>alert('Error creating account. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
            width: 100%;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<h1>Login</h1>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit" name="login">Login</button>
</form>

<h2>Register</h2>
<form method="POST">
    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="email" name="register_email" placeholder="Email" required />
    <input type="password" name="register_password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <button type="submit" name="register">Register</button>
</form>

</body>
</html>
