<?php
session_start();
include("database.php");

// Check if user is logged in and has user role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

// Fetch user details
$email_escaped = mysqli_real_escape_string($conn, $_SESSION['email']);
$query = "SELECT name, email, profile_picture FROM users WHERE email = '$email_escaped'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $error = "Error: User data not found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="registration-container">
        <img class="sample-img" src="https://res.cloudinary.com/darsfmavs/image/upload/v1747111881/xpg7zpxqj8diuurkhzk8.png" alt="User Dashboard Image"/>
        <div class="user-container">
            <?php if (isset($user['profile_picture']) && $user['profile_picture']): ?>
                <img class="profile-pic" src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture"/>
            <?php endif; ?>
            <h1 class="heading">User Dashboard</h1>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php else: ?>
                <div class="user-profile">
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <a href="logout.php" class="logout-button">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>