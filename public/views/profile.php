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
</head>
<body>
<?php include __DIR__ . '/shared/header.php'; ?>

<main>
<div class="signup-container">
    <h2 class="text-center">My Profile</h2>
    
    <!-- User Information Section -->
    <div class="profile-section">
        <h4 class="section-title">Account Information</h4>
        <div class="user-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Email:</strong> <span id="user-email">Loading...</span></p>
            <p><strong>Member Since:</strong> <span id="member-since">Loading...</span></p>
        </div>
    </div>

    <!-- Change Password Section -->
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

    <!-- Email Preferences Section -->
    <div class="profile-section">
        <h4 class="section-title">Email Preferences</h4>
        <form id="preferences-form">
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="notify_comments" name="notify_comments" checked>
                <label class="form-check-label" for="notify_comments">
                    Notify me when someone comments on my images
                </label>
            </div>
            
            <button type="submit" class="btn w-100 btn-accent mb-3">Save Preferences</button>
        </form>
    </div>

    <!-- Account Stats Section -->
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

    <!-- Danger Zone -->
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

<style>
.profile-section {
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    color: var(--color-accent);
    margin-bottom: 15px;
    font-size: 1.2rem;
    font-weight: 600;
}

.user-info p {
    margin-bottom: 8px;
    color: var(--color-text-light);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
    background: rgba(255, 255, 255, 0.05);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--color-accent);
}

.stat-label {
    display: block;
    font-size: 0.9rem;
    color: var(--color-text-light);
    margin-top: 5px;
}

.form-check-input:checked {
    background-color: var(--color-accent);
    border-color: var(--color-accent);
}

.form-check-input:focus {
    border-color: var(--color-accent);
    box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.4);
}

.form-check-label {
    color: var(--color-text-light);
}

.danger-zone {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 8px;
    padding: 20px;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.message {
    padding: 12px;
    margin: 15px 0;
    border-radius: 6px;
    font-weight: 500;
}

.message-success {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.message-error {
    background-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.profile-container {
    max-width: 450px;
    margin: 50px auto;
    
    background: var(--color-medium-bg); 
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 8px 30px var(--color-shadow); 
    border: 1px solid rgba(255, 255, 255, 0.1); 
    color: var(--color-text-light);
    
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px var(--color-shadow);
}

.profile-container h2 {
    color: var(--color-accent);
    margin-bottom: 30px;
    font-weight: 700;
}
</style>

</body>
</html>