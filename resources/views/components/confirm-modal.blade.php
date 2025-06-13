<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">{{ $title ?? 'Confirm Delete' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $message ?? 'Are you sure you want to delete this item? This action cannot be undone.' }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ $cancelText ?? 'No, keep it' }}
                </button>
                <button type="button" class="btn btn-danger" onclick="submitDeleteForm('{{ $formId }}')">
                    {{ $confirmText ?? 'Yes, delete it' }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.custom-modal .modal-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 8px 30px rgba(0, 191, 166, 0.15);
}

.custom-modal .modal-header {
    padding: 1.5rem 1.5rem 0.5rem;
}

.custom-modal .modal-body {
    padding: 1.5rem;
    color: #2d3748;
}

.custom-modal .modal-footer {
    padding: 0.5rem 1.5rem 1.5rem;
}

.custom-modal .btn-danger {
    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.custom-modal .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(231, 76, 60, 0.25);
}

.custom-modal .btn-light {
    background: #e2e8f0;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.custom-modal .btn-light:hover {
    background: #cbd5e1;
    transform: translateY(-2px);
}
</style>

<script>
window.confirmDelete = function(formId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '{{ $title ?? "Confirm Delete" }}',
            text: '{{ $message ?? "Are you sure you want to delete this item? This action cannot be undone." }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ $confirmText ?? "Yes, delete it" }}',
            cancelButtonText: '{{ $cancelText ?? "No, keep it" }}',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true,
            background: 'rgba(255, 255, 255, 0.9)',
            backdrop: 'rgba(0, 0, 0, 0.4)'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    } else {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }
}

window.submitDeleteForm = function(formId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    modal.hide();
    document.getElementById(formId).submit();
}
</script>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">{{ $title ?? 'Confirm Action' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $message ?? 'Are you sure you want to proceed?' }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ $cancelText ?? 'Cancel' }}
                </button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">
                    {{ $confirmText ?? 'Confirm' }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(formId) {
    // Try to use SweetAlert2 if available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '{{ $title ?? "Confirm Action" }}',
            text: '{{ $message ?? "Are you sure you want to proceed?" }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ $confirmText ?? "Confirm" }}',
            cancelButtonText: '{{ $cancelText ?? "Cancel" }}',
            reverseButtons: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    } else {
        // Fallback to Bootstrap Modal
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const confirmBtn = document.getElementById('confirmActionBtn');
        
        // Remove any existing click handlers
        confirmBtn.replaceWith(confirmBtn.cloneNode(true));
        
        // Add new click handler
        document.getElementById('confirmActionBtn').addEventListener('click', function() {
            document.getElementById(formId).submit();
            modal.hide();
        });
        
        modal.show();
    }
}
</script> 