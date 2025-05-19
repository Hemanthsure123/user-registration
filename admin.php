<?php
session_start();
include("database.php");

// Check if user is logged in and has admin role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch admin details
$email_escaped = mysqli_real_escape_string($conn, $_SESSION['email']);
$query = "SELECT name, email, profile_picture FROM users WHERE email = '$email_escaped'";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    $error = "Error: Admin data not found.";
}

// Fetch all users
$query = "SELECT id, name, email, user_type, profile_picture FROM users";
$result = mysqli_query($conn, $query);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $query = "SELECT profile_picture FROM users WHERE id = '$delete_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    if ($user && $user['profile_picture'] && file_exists($user['profile_picture'])) {
        unlink($user['profile_picture']);
    }
    $query = "DELETE FROM users WHERE id = '$delete_id'";
    mysqli_query($conn, $query);
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="registration-container">
        <img class="sample-img" src="https://res.cloudinary.com/darsfmavs/image/upload/v1747111881/xpg7zpxqj8diuurkhzk8.png" alt="Admin Dashboard Image"/>
        <div class="admin-container">
            <?php if (isset($admin['profile_picture']) && $admin['profile_picture']): ?>
                <img class="profile-pic" src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture"/>
            <?php endif; ?>
            <h1 class="heading">Admin Dashboard</h1>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php else: ?>
                <div class="admin-welcome">
                    <h2>Welcome, <?php echo htmlspecialchars($admin['name']); ?>!</h2>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                </div>
                <hr>
                <h3>Registered Users</h3>
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Profile Picture</th>
                                    <th class="actions-column">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                                        <td>
                                            <?php if ($user['profile_picture']): ?>
                                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="table-profile-pic"/>
                                            <?php else: ?>
                                                None
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions-column">
                                            <div class="action-buttons">
                                                <a href="#" class="action-button view-button" onclick="showPopup(<?php echo htmlspecialchars(json_encode($user)); ?>)">View</a>
                                                <a href="update.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="action-button update-button">Update</a>
                                                <a href="admin.php?delete_id=<?php echo htmlspecialchars($user['id']); ?>" class="action-button delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <a href="logout.php" class="logout-button">Logout</a>
            <?php endif; ?>
            <!-- Popup for user details -->
            <div id="userPopup" class="popup">
                <div class="popup-content">
                    <span class="close-button" onclick="closePopup()">×</span>
                    <h2>User Details</h2>
                    <div id="popup-details"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showPopup(user) {
            const popup = document.getElementById('userPopup');
            const details = document.getElementById('popup-details');
            details.innerHTML = `
                <p><strong>ID:</strong> ${user.id}</p>
                <p><strong>Name:</strong> ${user.name}</p>
                <p><strong>Email:</strong> ${user.email}</p>
                <p><strong>User Type:</strong> ${user.user_type}</p>
                ${user.profile_picture ? `<img src="${user.profile_picture}" alt="Profile Picture" class="popup-profile-pic"/>` : '<p>No Profile Picture</p>'}
            `;
            popup.style.display = 'block';
        }

        function closePopup() {
            const popup = document.getElementById('userPopup');
            popup.classList.add('popup-closing');
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('popup-closing');
            }, 300);
        }

        // Close popup when clicking outside
        window.onclick = function(event) {
            const popup = document.getElementById('userPopup');
            if (event.target === popup) {
                closePopup();
            }
        }
    </script>
</body>
</html>