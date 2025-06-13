document.getElementById('avatar').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

function confirmDeleteAccount() {
    notifications.confirm(
        'Are you sure you want to delete your account? This action cannot be undone.',
        'Delete Account',
        'Yes, delete my account',
        'No, keep my account'
    ).then((result) => {
        if (result) {
            notifications.info('Account deletion will be implemented');
        }
    });
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
}); 