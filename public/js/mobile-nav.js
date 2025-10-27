
function toggleMobileNav() {
    const navContainer = document.getElementById('navbarNav');
    
    const togglerButton = document.getElementById('navbar-toggler-button');

    navContainer.classList.toggle('collapse');

    const isHidden = navContainer.classList.contains('collapse');

    togglerButton.setAttribute('aria-expanded', !isHidden);
}