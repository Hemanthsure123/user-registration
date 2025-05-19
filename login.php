<?php
include("database.php");
session_start();

$msg = "";
$errors = [];
$logout_message = $_SESSION['logout_message'] ?? '';
unset($_SESSION['logout_message']);
$email = "";

if (isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $email = strtolower($email);
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $query = "SELECT id, email, password, user_type, profile_picture FROM users WHERE email = '$email_escaped'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['email'] = $user['email'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['user_type'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                if ($user['user_type'] === 'user') {
                    header('Location: user.php');
                    exit;
                } elseif ($user['user_type'] === 'admin') {
                    header('Location: admin.php');
                    exit;
                }
            } else {
                $errors[] = "Incorrect email or password.";
            }
        } else {
            $errors[] = "Incorrect email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="registration-container">
        <img class="sample-img" src="https://res.cloudinary.com/darsfmavs/image/upload/v1747111881/xpg7zpxqj8diuurkhzk8.png" alt="Login Image"/>
        <div class="login-form-container"> 
            <form class="form" action="" method="POST">
                <h1 class="heading">Login</h1>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($logout_message): ?>
                    <p class="success-message"><?php echo htmlspecialchars($logout_message); ?></p>
                <?php endif; ?>
                <label for="email" class="user-name">EMAIL</label>
                <input type="email" id="email" placeholder="Enter your email..." class="input-fields" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                <label for="password" class="user-name">PASSWORD</label>
                <input type="password" id="password" placeholder="Enter your password..." class="input-fields" name="password" required />
                <button type="submit" class="register-button" name="submit">Login</button>
                <p class="login-text">Don't have an account? <a href="register.php">Register now</a></p>
            </form>
        </div>
    </div>
</body>
</html>