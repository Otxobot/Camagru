<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home | Camagru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <link rel="stylesheet" href="../css/styles-home.css">
  <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Csrf::getToken(), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>

    <?php include __DIR__ . '/shared/header.php'; ?>

  <main>
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Camagru</h1>
        <p class="hero-subtitle">Capture, Create, and Share Your Moments</p>
        <p class="hero-description">A fun and interactive photo-sharing platform where you can showcase your creativity</p>
        <div class="hero-buttons">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/signup" class="btn btn-primary btn-lg">Get Started</a>
            <a href="/gallery" class="btn btn-outline-primary btn-lg">View Gallery</a>
          <?php else: ?>
            <a href="/dashboard" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <a href="/gallery" class="btn btn-outline-primary btn-lg">Browse Gallery</a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="features-section">
      <h2>Why Choose Camagru?</h2>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">📸</div>
          <h3>Upload & Share</h3>
          <p>Easily upload and share your favorite photos with the community</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">✨</div>
          <h3>Express Yourself</h3>
          <p>Add stickers and effects to personalize your photos and make them unique</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">❤️</div>
          <h3>Engage & Connect</h3>
          <p>Like and comment on photos from other users and build connections</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🎨</div>
          <h3>Creative Community</h3>
          <p>Join a vibrant community of creative individuals sharing their passion</p>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="cta-content">
        <h2>Ready to Join the Creative Revolution?</h2>
        <p>Start sharing your moments with the world today</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="/signup" class="btn btn-light btn-lg">Create Your Account</a>
        <?php else: ?>
          <a href="/dashboard" class="btn btn-light btn-lg">Start Creating</a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/shared/footer.php'; ?>

  <script src="/js/mobile-nav.js"></script>
  <script src="/js/signup.js"></script>
  <script src="/js/logout.js"></script>
</body>
</html>
