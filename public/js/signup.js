// Simple signup form handler
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
});

async function handleSignup(event) {
    event.preventDefault();
    console.log("event", event);

    console.log("look here bix");
    
    // Get form data
    const formData = new FormData(event.target);
    const signupData = {
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password')
    };
    console.log("signupData", signupData);
    
    // Basic validation
    if (!signupData.username || !signupData.email || !signupData.password) {
        showMessage('All fields are required', 'error');
        return;
    }
    
    if (!isValidEmail(signupData.email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    if (signupData.password.length < 6) {
        showMessage('Password must be at least 6 characters long', 'error');
        return;
    }
    
    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Creating account...';
    submitButton.disabled = true;
    
    try {
        console.log("pasa por aqui");
        // Send signup request
        const response = await fetch('/api/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(signupData)
        });
        console.log("aver por auqi")
        const result = await response.json();

        console.log("response", response);
        
        if (response.ok) {
            showMessage('Account created successfully!', 'success');
            event.target.reset(); // Clear form
        } else {
            showMessage(result.error || 'Signup failed', 'error');
        }
        
    } catch (error) {
        showMessage('Server error. Please try again.', 'error');
        console.error('Signup error:', error);
    } finally {
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showMessage(message, type) {
    // Remove existing messages
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    // Insert at top of form or body
    const form = document.getElementById('signup-form');
    if (form) {
        form.insertBefore(messageDiv, form.firstChild);
    } else {
        document.body.insertBefore(messageDiv, document.body.firstChild);
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}