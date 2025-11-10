// Add this to your mobile-nav.js or a similar file

document.addEventListener('DOMContentLoaded', function() {
    // ... (your existing toggleMobileNav function can be here) ...

    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }
});

async function handleLogout(event) {
    event.preventDefault(); // Stop the link from trying to navigate

    try {
        const response = await fetch('/api/logout', {
            method: 'POST',
        });

        const result = await response.json();

        if (response.ok) {
            // Success! Redirect to the homepage
            window.location.href = '/';
        } else {
            alert(result.error || 'Logout failed.');
        }

    } catch (error) {
        console.error('Logout error:', error);
        alert('Server error during logout.');
    }
}