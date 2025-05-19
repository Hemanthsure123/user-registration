<?php
session_start();
include("database.php");

$msg = "";
$errors = [];
$name = $email = $usertype = "";

if (isset($_POST["submit"])) {
    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usertype = $_POST['user_type'] ?? '';
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if (empty($name)) {
        $errors[] = "Username is required.";
    } elseif (strlen($name) < 3 || strlen($name) > 50) {
        $errors[] = "Username must be 3-50 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($email) > 255) {
        $errors[] = "Email is too long.";
    }

    if (!in_array($usertype, ['user', 'admin'])) {
        $errors[] = "Invalid user type selected.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $cpassword) {
        $errors[] = "Passwords do not match.";
    }

    $profile_picture = $_FILES['profile_picture'] ?? null;
    $profile_picture_path = null;
    if (!$profile_picture || $profile_picture['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Profile picture is required.";
    } else {
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (!in_array($profile_picture['type'], $allowed_types)) {
            $errors[] = "Profile picture must be PNG, JPEG, or JPG.";
        } elseif ($profile_picture['size'] > $max_size) {
            $errors[] = "Profile picture must be less than 5MB.";
        } elseif ($profile_picture['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading profile picture.";
        } else {
            $ext = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $profile_picture_path = 'uploads/' . $filename;
            if (!move_uploaded_file($profile_picture['tmp_name'], $profile_picture_path)) {
                $errors[] = "Failed to save profile picture.";
            }
        }
    }

    if (empty($errors)) {
        $email = strtolower($email);
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $query = "SELECT id FROM users WHERE email = '$email_escaped'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Email already exists.";
        }
    }

    if (empty($errors)) {
        $name_escaped = mysqli_real_escape_string($conn, $name);
        $usertype_escaped = mysqli_real_escape_string($conn, $usertype);
        $profile_picture_path_escaped = mysqli_real_escape_string($conn, $profile_picture_path);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password, user_type, profile_picture) VALUES ('$name_escaped', '$email_escaped', '$hashed_password', '$usertype_escaped', '$profile_picture_path_escaped')";
        if (mysqli_query($conn, $query)) {
            $msg = "Registration successful! Please log in.";
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Error registering user.";
            error_log("Database error: " . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="registration-container">
        <img class="sample-img" src="https://res.cloudinary.com/darsfmavs/image/upload/v1747111881/xpg7zpxqj8diuurkhzk8.png" alt="Registration Image"/>
        <div class="form-container"> 
            <form class="form" action="" method="POST" enctype="multipart/form-data">
                <h1 class="heading">Registration</h1>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($msg): ?>
                    <p class="success-message"><?php echo htmlspecialchars($msg); ?></p>
                <?php endif; ?>
                <label for="username" class="user-name">USERNAME</label>
                <input type="text" id="username" placeholder="Enter your username..." class="input-fields" name="user_name" value="<?php echo htmlspecialchars($name); ?>" required />
                <label for="email" class="user-name">EMAIL</label>
                <input type="email" id="email" placeholder="Enter your email..." class="input-fields" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                <label for="usertype" class="user-name">USER TYPE</label>
                <select id="usertype" class="input-fields" name="user_type" required>
                    <option value="user" <?php echo $usertype === 'user' ? 'selected' : ''; ?>>USER</option>
                    <option value="admin" <?php echo $usertype === 'admin' ? 'selected' : ''; ?>>ADMIN</option>
                </select>
                <label for="password" class="user-name">PASSWORD</label>
                <input type="password" id="password" placeholder="Enter your password..." class="input-fields" name="password" required />
                <label for="re-enter-password" class="user-name">CONFIRM PASSWORD</label>
                <input type="password" id="re-enter-password" placeholder="Confirm your password..." class="input-fields" name="cpassword" required />
                <label for="profile_picture" class="user-name">PROFILE PICTURE</label>
                <input type="file" id="profile_picture" class="input-fields" name="profile_picture" accept="image/png,image/jpeg,image/jpg" required />
                <button type="submit" class="register-button" name="submit">Register</button>
                <p class="login-text">Already have an account? <a href="login.php">Login now</a></p>
            </form>
        </div>
    </div>
</body>
</html>