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
  <title>Logout | Camagru</title>    
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/styles-home.css">
  <link rel="stylesheet" href="../css/styles-signup.css">
</head>
<body>
<?php include __DIR__ . '/shared/header.php'; ?>

<main>
<div class="signup-container">
<h2 class="text-center">Logout</h2>
     <form id="logout-form" action="/api/logout" method="POST">
        <p class="text-center mb-4">Are you sure you want to logout?</p>
        
        <button type="submit" class="btn w-100 btn-accent">Logout</button>
        
        <!-- <div class="text-center mt-3">
            <a href="/" class="btn btn-secondary">Cancel</a>
        </div> -->
        
        <div id="formError" class="error-message"></div>
    </form>
</div>
</main>

<?php include __DIR__ . '/shared/footer.php'; ?>

<script src="/js/logout.js"></script>
<script src="/js/mobile-nav.js"></script>

</body>
</html>