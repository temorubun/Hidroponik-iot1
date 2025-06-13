// Notification Helper Functions
window.notifications = {
    // Success notification
    success: (message, title = 'Success') => {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    },

    // Error notification
    error: (message, title = 'Error') => {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    },

    // Warning notification
    warning: (message, title = 'Warning') => {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    },

    // Info notification
    info: (message, title = 'Info') => {
        Swal.fire({
            icon: 'info',
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    },

    // Confirmation dialog
    confirm: async (message, title = 'Are you sure?', confirmText = 'Yes', cancelText = 'No') => {
        const result = await Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#00BFA6',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusConfirm: false,
            focusCancel: true,
            background: 'rgba(255, 255, 255, 0.9)',
            backdrop: 'rgba(0, 0, 0, 0.4)',
            customClass: {
                container: 'custom-swal-container',
                popup: 'custom-swal-popup',
                header: 'custom-swal-header',
                title: 'custom-swal-title',
                closeButton: 'custom-swal-close',
                content: 'custom-swal-content',
                input: 'custom-swal-input',
                actions: 'custom-swal-actions',
                confirmButton: 'custom-swal-confirm',
                cancelButton: 'custom-swal-cancel',
                footer: 'custom-swal-footer'
            }
        });
        return result.isConfirmed;
    },

    // Alert dialog
    alert: (message, title = 'Alert') => {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#00BFA6',
            background: 'rgba(255, 255, 255, 0.9)',
            backdrop: 'rgba(0, 0, 0, 0.4)',
            customClass: {
                container: 'custom-swal-container',
                popup: 'custom-swal-popup',
                header: 'custom-swal-header',
                title: 'custom-swal-title',
                closeButton: 'custom-swal-close',
                content: 'custom-swal-content',
                actions: 'custom-swal-actions',
                confirmButton: 'custom-swal-confirm',
                footer: 'custom-swal-footer'
            }
        });
    },

    // Custom notification with options
    custom: (options) => {
        return Swal.fire(options);
    }
}; 