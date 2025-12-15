
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

// function showMessage(message, type) {

//     const existingMessage = document.querySelector('.message');
//     if (existingMessage) {
//         existingMessage.remove();
//     }
    
//     const messageDiv = document.createElement('div');
//     messageDiv.className = `message message-${type}`;
//     messageDiv.textContent = message;
    
//     const form = document.getElementById('signup-form');
//     if (form) {
//         form.insertBefore(messageDiv, form.firstChild);
//     } else {
//         document.body.insertBefore(messageDiv, document.body.firstChild);
//     }
    
//     setTimeout(() => {
//         if (messageDiv.parentNode) {
//             messageDiv.remove();
//         }
//     }, 5000);
// }

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