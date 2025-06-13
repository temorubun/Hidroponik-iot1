document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    // Preview image before upload
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select an image file',
                    icon: 'error'
                });
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            }
            reader.readAsDataURL(file);

            // Automatically submit form when file is selected
            const form = avatarInput.closest('form');
            form.submit();
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

function confirmDeleteAccount() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! All your data will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete my account!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the delete account form
            document.getElementById('delete-account-form').submit();
        }
    });
} 