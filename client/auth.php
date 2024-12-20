<?php
include '../php/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
    $stmt->execute([$email]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher && password_verify($password, $teacher['password'])) {
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['role'] = 'teacher';
        $_SESSION['id'] = $teacher['id'];  

        $_SESSION['teacher_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
        header('Location: index.php');
        exit;
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['register_email'];
    $password = $_POST['register_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        die("<script>alert('Passwords do not match.');</script>");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

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
    <title>Teacher Portal Login</title>
    <style>
        /* Color Scheme */
        :root {
            --light-bg: #F1F6F9;
            --primary: #394867;
            --secondary: #212A3E;
            --accent: #9BA4B5;
        }

        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: var(--secondary);
        }

        h1 {
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--accent);
            border-radius: 5px;
            font-size: 1rem;
            color: var(--secondary);
        }

        button {
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: var(--secondary);
        }

        .separator {
            text-align: center;
            margin: 20px 0;
            color: var(--accent);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Teacher Portal</h1>

    <!-- Login Form -->
    <h2>Login</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" name="login">Login</button>
    </form>

    <div class="separator">or</div>

    <!-- Registration Form -->
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required />
        <input type="text" name="last_name" placeholder="Last Name" required />
        <input type="email" name="register_email" placeholder="Email" required />
        <input type="password" name="register_password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <button type="submit" name="register">Register</button>
    </form>
</div>

</body>
</html>
