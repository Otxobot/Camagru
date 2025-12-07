
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
});

async function handleSignup(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const signupData = {
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password')
    };
    console.log("signupData", signupData);
    
    if (!signupData.username || !signupData.email || !signupData.password) {
        showMessage('All fields are required', 'error');
        return;
    }
    
    if (!isValidEmail(signupData.email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    // DESCOMENTAR ESTO, PERO PARA PRUEBAS ESTA COMENTADO
    // if (!isComplexPassword(signupData.password)) {
    //     const complexityError = getPasswordComplexityError(signupData.password);
    //     showMessage(complexityError || 'Password does not meet complexity requirements', 'error');
    //     return;
    // }
    
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Creating account...';
    submitButton.disabled = true;
    
    try {
        const response = await fetch('/api/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(signupData)
        });
        console.log("aver por auqi")
        const result = await response.json();

        
        if (response.ok) {
            showMessage('Account created successfully! Please check your email to verify your account.', 'success');
            event.target.reset();
        } else {
            showMessage(result.error || 'Signup failed', 'error');
        }
        
    } catch (error) {
        showMessage('Server error. Please try again.', 'error');
        console.error('Signup error:', error);
    } finally {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
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

function isComplexPassword(password) {

    if (password.length < 8) {
        return false;
    }

    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);

    return hasUpperCase && hasLowerCase && hasNumber;
}

function getPasswordComplexityError(password) {
    if (password.length < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!(/[A-Z]/.test(password))) {
        return 'Password must contain at least one uppercase letter.';
    }
    if (!(/[a-z]/.test(password))) {
        return 'Password must contain at least one lowercase letter.';
    }
    if (!(/[0-9]/.test(password))) {
        return 'Password must contain at least one number.';
    }
    return '';
}