
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const forgotPasswordLink = document.querySelector('a[href="/forgot-password"]');

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();

            showForgotPasswordModal();
        });
    }
});

async function handleLogin(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const loginData = {
        email: formData.get('email'),
        password: formData.get('password')
    };

    if (!loginData.email  || !loginData.password) {
        showMessage("All fields are required", 'error');
    }

    try {

        showMessage("Trying login", 'success'); 
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(loginData)
        });

        const result = await response.json();

        if (response.ok) {
            showMessage('Logged in successfully!', 'success');
            event.target.reset(); // Clear form
            window.location.href = '/';
        } else {
            showMessage(result.error || 'Login failed', 'error');
        }
    } catch (error) {
        showMessage('Server error. Please try again.', 'error');
        console.error('Login error:', error);
    }
}

function showForgotPasswordModal() {
    const email = prompt("Enter your email address:");
    if (email) {
        handleForgotEmail(email);
    }
}

function handleForgotEmail(email) {
    fetch('/forgot-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({email: email})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password reset email sent!');
        } else {
            alert(data.message || 'Error sending reset email');
        }
    })
    .catch(error => {
        console.error('Error', error);
        alert('An error ocurred. Please try again.');
    })
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