<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | Camagru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/styles-home.css">
  <link rel="stylesheet" href="../css/styles-signup.css">
</head>
<body>
<?php include __DIR__ . '/shared/header.php'; ?>

<main>
<div class="signup-container">
<h2 class="text-center">Create Your Account</h2>
     <form id="signup-form" action="/register" method="POST">
        
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" 
                   required minlength="3" maxlength="50" placeholder="Enter your desired username">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" 
                   required placeholder="you@example.com">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" 
                   required minlength="8" placeholder="Minimum 8 characters">
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                   required placeholder="Re-enter password">
        </div>

        <button type="submit" class="btn w-100 btn-accent">Sign Up</button>
        
        <div id="formError" class="error-message"></div>
    </form>
</div>
</main>

<?php include __DIR__ . '/shared/footer.php'; ?>

<script src="/js/signup.js"></script>
<script src="/js/mobile-nav.js"></script>

</body>
</html>
