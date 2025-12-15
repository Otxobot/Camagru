
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
    
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-weight: 500;
        max-width: 90vw;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    `;
    
    if (type === 'success') {
        messageDiv.style.backgroundColor = '#10b981';
        messageDiv.style.color = 'white';
    } else if (type === 'error') {
        messageDiv.style.backgroundColor = '#ef4444';
        messageDiv.style.color = 'white';
    } else if (type === 'info') {
        messageDiv.style.backgroundColor = '#3b82f6';
        messageDiv.style.color = 'white';
    }
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 300);
    }, 5000);
}