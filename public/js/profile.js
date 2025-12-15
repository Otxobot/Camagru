document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('password-form');
    const usernameForm = document.getElementById('username-form');
    const emailForm = document.getElementById('email-form');
    const preferencesForm = document.getElementById('preferences-form');
    const deleteAccountBtn = document.getElementById('delete-account-btn');
    const newUsernameInput = document.getElementById('new_username');
    // const changeUsernameBtn = document.getElementById('change-username-button');
    const formMessage = document.getElementById('formMessage');

    // loadUserStats();

    if (usernameForm && newUsernameInput) {
        usernameForm.addEventListener('submit', handleUsernameChange);
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', handlePasswordChange);
    }

    if (emailForm) {
        emailForm.addEventListener('submit', handleEmailChange);
    }

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', handleAccountDeletion);
    }
})

async function handleUsernameChange(e) {
    e.preventDefault();

    const newUsernameInput = document.getElementById('new_username');
    const newUsername = newUsernameInput.value.trim();
    const currentUsernameInput = document.getElementById('current_username');
    const currentUsername = currentUsernameInput.value.trim();
    
    if (!newUsername) {
        showMessage('Please enter a new username', 'error');
        return;
    }

    if (currentUsername === newUsername) {
        showMessage('Please enter your current username', 'error');
        return;
    }

    try {
        const response = await fetch('/api/profile/update-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                current_username: currentUsername,
                new_username: newUsername 
            })
        });

        const responseText = await response.text();

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            showMessage('Server returned invalid response', 'error');
            return;
        }

        if (data.success) {
            showMessage('Username updated successfully!', 'success');
            newUsernameInput.value = '';

            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } else {
            showMessage(data.message || 'Failed to update username', 'error');
        }
    } catch (error) {
        console.error('Error updating username:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

async function handlePasswordChange(e) {

    e.preventDefault();
    
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_new_password').value;
    const currentEmailInput = document.getElementById('current_email');
    const currentEmail = currentEmailInput.value.trim();

    const passwordForm = document.getElementById('password-form');

    console.log(newPassword, confirmPassword);

    if (newPassword !== confirmPassword) {
        showMessage('New passwords do not match', 'error');
        return;
    }

    if (newPassword.length < 8) {
        showMessage('New password must be at least 8 characters long', 'error');
        return;
    }

    try {
        const response = await fetch('/api/profile/update-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                email: currentEmail
            })
        });

        const data = await response.json();

        if (data.success) {
            showMessage('Password updated successfully!', 'success');
            passwordForm.reset();
        } else {
            showMessage(data.message || 'Failed to update password', 'error');
        }
    } catch (error) {
        console.error('Error updating password:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

async function handleEmailChange(e) {
    e.preventDefault();

    console.log("event", e);

    const newEmailInput = document.getElementById('new_email');
    const newEmail = newEmailInput.value.trim();
    const currentEmailInput = document.getElementById('current_email');
    const currentEmail = currentEmailInput.value.trim();

    if (!newEmail) {
        showMessage('Please enter a new email', 'error');
        return;
    }

    if (currentEmail === newEmail) {
        showMessage('New email must be different from current email', 'error');
        return;
    }

    // Client-side email validation
    if (!validateEmail(newEmail)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }

    try {
        const response = await fetch('/api/profile/update-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                current_email: currentEmail,
                new_email: newEmail 
            })
        });

        const responseText = await response.text();

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            showMessage('Server returned invalid response', 'error');
            return;
        }

        if (data.success) {
            showMessage('Email updated successfully! Please check your new email to verify the change.', 'success');
            newEmailInput.value = '';

            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showMessage(data.message || 'Failed to update email', 'error');
        }
    } catch (error) {
        console.error('Error updating email:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function handleAccountDeletion() {
        const confirmed = confirm(
            'Are you absolutely sure you want to delete your account?\n\n' +
            'This action cannot be undone and will permanently delete:\n' +
            '• All your photos and posts\n' +
            '• All your comments and likes\n' +
            '• Your account information\n\n' +
            'Type "DELETE" in the next prompt to confirm.'
        );

        if (confirmed) {
            const finalConfirmation = prompt('Type "DELETE" to permanently delete your account:');
            
            if (finalConfirmation === 'DELETE') {
                deleteAccount();
            } else {
                showMessage('Account deletion cancelled', 'info');
            }
        }
    }

    // Delete account API call
    async function deleteAccount() {
        try {
            const response = await fetch('/api/profile/delete-account', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Your account has been permanently deleted.');
                window.location.href = '/';
            } else {
                showMessage(data.message || 'Failed to delete account', 'error');
            }
        } catch (error) {
            console.error('Error deleting account:', error);
            showMessage('An error occurred. Please try again.', 'error');
        }
    }

async function loadUserStats() {
    try {
        const response = await fetch('/api/profile/stats');
        const data = await response.json();

        if (data.success) {
            document.getElementById('images-count').textContent = data.stats.images_count || 0;
            document.getElementById('likes-received').textContent = data.stats.likes_received || 0;
            document.getElementById('comments-made').textContent = data.stats.comments_made || 0;
        }
    } catch (error) {
        console.error('Error loading user stats:', error);
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