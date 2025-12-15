<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | Camagru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/styles-home.css">
  <link rel="stylesheet" href="../css/styles-signup.css">
  <link rel="stylesheet" href="../css/styles-profile.css">
</head>
<body>
<?php include __DIR__ . '/shared/header.php'; ?>

<main>
<div class="profile-container">
    <h2 class="text-center">My Profile</h2>

    
        <div class="profile-section">
            <h4 class="section-title">Account Information</h4>
            <div class="user-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Email:</strong> <span id="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></span></p>
                <p><strong>Member Since:</strong> <span id="member-since"><?php echo htmlspecialchars($_SESSION['created_at'] = (new DateTime())->format('d-m-Y'))?></span></p>
            </div>
        </div>
        
        <div class="profile-section">
            <h4 class="section-title">Change username</h4>
            <form id="username-form">
                <div class="mb-3">
                    <label for="current_username" class="form-label">Current Username:</label>
                    <input type="username" id="current_username" class="form-control"
                            value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>

                    <label for="new_username" class="form-label">New Username:</label>
                    <input type="username" id="new_username" class="form-control"
                        required placeholder="Enter new username">
                </div>

                <button type="submit" class="btn w-100 btn-accent mb-3">Update Username</button>
            </form>
        </div>

        <div class="profile-section">
            <h4 class="section-title">Change Password</h4>
            <form id="password-form">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" 
                        required placeholder="Enter current password">
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" 
                        required minlength="8" placeholder="Minimum 8 characters">
                </div>

                <div class="mb-4">
                    <label for="confirm_new_password" class="form-label">Confirm New Password:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" 
                        required placeholder="Re-enter new password">
                </div>

                <button type="submit" class="btn w-100 btn-accent mb-3">Update Password</button>
            </form>
        </div>

        <div class="profile-section">
            <h4 class="section-title">Change email</h4>
            <form id="email-form">
                <div class="mb-3">
                    <label for="current_email" class="form-label">Current Email:</label>
                    <input type="email" id="current_email" class="form-control"
                            value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                </div>

                <div class="mb-4">
                    <label for="new_email" class="form-label">New Email:</label>
                    <input type="username" id="new_email" class="form-control"
                        required placeholder="Enter new email">
                </div>
                
                <button type="submit" class="btn w-100 btn-accent mb-3">Update email</button>
            </form>
        </div>
        
        <div class="profile-section">
            <h4 class="section-title">My Statistics</h4>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number" id="images-count">-</span>
                    <span class="stat-label">Images Posted</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="likes-received">-</span>
                    <span class="stat-label">Likes Received</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="comments-made">-</span>
                    <span class="stat-label">Comments Made</span>
                </div>
            </div>
        </div>

        <div class="profile-section danger-zone">
            <h4 class="section-title text-danger">Danger Zone</h4>
            <p class="text-muted small">These actions are permanent and cannot be undone.</p>
            <button type="button" class="btn btn-outline-danger w-100" id="delete-account-btn">
                Delete My Account
            </button>
        </div>

        <div id="formMessage" class="error-message"></div>
</div>
</main>

<?php include __DIR__ . '/shared/footer.php'; ?>

<script src="/js/profile.js"></script>
<script src="/js/mobile-nav.js"></script>
<script src="/js/logout.js"></script>

</body>
</html>