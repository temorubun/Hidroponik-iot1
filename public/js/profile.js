// Profile Image Upload Handler
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-user-img').src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});

// Security Functions
function enableTwoFactor() {
    notifications.confirm(
        'Do you want to enable two-factor authentication?',
        'Enable 2FA',
        'Yes, enable it',
        'No, cancel'
    ).then((result) => {
        if (result) {
            setupTwoFactor();
        }
    });
}

function disableTwoFactor() {
    notifications.confirm(
        'Are you sure you want to disable two-factor authentication? This will make your account less secure.',
        'Disable 2FA',
        'Yes, disable it',
        'No, keep it enabled'
    ).then((result) => {
        if (result) {
            disableTwoFactorAuth();
        }
    });
}

function logoutOtherDevices() {
    notifications.confirm(
        'Are you sure you want to log out all other devices? This will terminate all other active sessions.',
        'Logout Other Devices',
        'Yes, logout all',
        'No, cancel'
    ).then((result) => {
        if (result) {
            logoutOtherDevicesAction();
        }
    });
}

function confirmDeleteAccount() {
    notifications.confirm(
        'WARNING: This action cannot be undone. Are you absolutely sure you want to delete your account?',
        'Delete Account',
        'Yes, delete my account',
        'No, keep my account'
    ).then((result) => {
        if (result) {
            notifications.confirm(
                'Last warning: All your data will be permanently deleted. Continue?',
                'Final Confirmation',
                'Yes, permanently delete',
                'No, cancel'
            ).then((finalResult) => {
                if (finalResult) {
                    deleteAccount();
                }
            });
        }
    });
}

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});

// Alert Auto-dismiss
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000); // Auto dismiss after 5 seconds
    });
});

function setupTwoFactor() {
    notifications.info('Two-factor authentication setup will be implemented here');
}

function disableTwoFactor() {
    notifications.warning('Two-factor authentication will be disabled');
}

function logoutOtherDevices() {
    notifications.info('Other devices will be logged out');
}

function confirmDeleteAccount() {
    notifications.info('Account deletion will be implemented');
} 