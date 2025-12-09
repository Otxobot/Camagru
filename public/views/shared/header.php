
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fs-4 fw-bold text-camagru-accent" href="/">Camagru</a>

            <button class="navbar-toggler" type="button" 
                    id="navbar-toggler-button"
                    onclick="toggleMobileNav()"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/gallery">Gallery</a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">My Dashboard</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/profile" role="button">Profile</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="logout-button" role="button" onclick="handleLogout(event)">Logout</a>
                        </li>


                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/signup">Signup</a> </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>