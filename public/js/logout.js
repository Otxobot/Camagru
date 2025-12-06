
document.addEventListener('DOMContentLoaded', function() {
    const logoutForm = document.getElementById('logout-form');

    if (logoutForm) {
        logoutForm.addEventListener('click', handleLogout);
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
            showMessage("Logged out succesfully");
            window.location.href = '/';
        } else {
            alert(result.error || 'Logout failed.');
            showMessage(result.error || 'Logout failed', 'error');
        }

    } catch (error) {
        showMessage('Server error. Please try again.', 'error');
        console.error('Logout error:', error);
    }
}

function showMessage(message, type) {

    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    const form = document.getElementById('signup-form');
    if (form) {
        form.insertBefore(messageDiv, form.firstChild);
    } else {
        document.body.insertBefore(messageDiv, document.body.firstChild);
    }
    
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}