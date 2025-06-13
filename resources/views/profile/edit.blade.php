@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('title')
            <span class="gradient-text">Profile Settings</span>
        @endslot
        @slot('subtitle')
            <span class="text-muted">Manage your account settings and security preferences</span>
        @endslot
        @slot('icon', 'fas fa-user-circle')

        <!-- Delete Account Modal -->
        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="deleteAccountModalLabel">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Account
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>WARNING:</strong> This action cannot be undone. All your data will be permanently deleted.
                        </div>
                        <form id="confirmDeleteForm">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Please enter your password to confirm:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" required>
                                </div>
                                <div class="invalid-feedback" id="password-error"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            No, keep my account
                        </button>
                        <button type="button" class="btn btn-danger" onclick="submitDeleteAccount()">
                            Yes, permanently delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Avatar Section -->
                            <div class="text-center mb-4" data-aos="zoom-in" data-aos-delay="200">
                                <div class="position-relative d-inline-block">
                                    <div class="profile-avatar-wrapper">
                                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                                             alt="Profile Picture" 
                                             class="profile-user-img img-fluid rounded-circle"
                                             id="avatar-preview"
                                             style="width: 200px; height: 200px; object-fit: cover;">
                                        <label for="avatar" class="profile-avatar-upload">
                                            <i class="fas fa-camera gradient-text"></i>
                                            <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <h3 class="profile-username h4 mb-2">{{ Auth::user()->name }}</h3>
                                <p class="text-muted mb-3">Member since {{ Auth::user()->created_at->format('M Y') }}</p>

                                @error('avatar')
                                    <span class="text-danger d-block small">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Name Field -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="300">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i>
                                    <span class="text-label">Name</span>
                                </label>
                                <div class="input-group themed-input">
                                    <span class="input-group-text"><i class="fas fa-user gradient-text"></i></span>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                           name="name" value="{{ old('name', Auth::user()->name) }}" required autocomplete="name"
                                           placeholder="Enter your name">
                                    
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="400">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    <span class="text-label">Email Address</span>
                                </label>
                                <div class="input-group disabled-input">
                                    <span class="input-group-text"><i class="fas fa-envelope gradient-text"></i></span>
                                    <input id="email" type="email" class="form-control" 
                                           value="{{ old('email', Auth::user()->email) }}" readonly disabled>
                                </div>
                                <small class="text-muted">Email address cannot be changed</small>
                            </div>

                            <!-- Telegram Chat ID Field -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="500">
                                <label for="telegram_chat_id" class="form-label">
                                    <i class="fab fa-telegram"></i>
                                    <span class="text-label">Telegram Chat ID</span>
                                    <i class="fas fa-question-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       title="Find your Chat ID by messaging @get_id_bot on Telegram"></i>
                                </label>
                                <div class="input-group themed-input">
                                    <span class="input-group-text"><i class="fab fa-telegram gradient-text"></i></span>
                                    <input id="telegram_chat_id" type="text" 
                                           class="form-control @error('telegram_chat_id') is-invalid @enderror" 
                                           name="telegram_chat_id" 
                                           value="{{ old('telegram_chat_id', Auth::user()->telegram_chat_id) }}"
                                           placeholder="Example: 123456789">
                                    
                                    @error('telegram_chat_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="text-muted">Used for receiving alerts and notifications</small>
                            </div>

                            <div class="d-grid" data-aos="fade-up" data-aos-delay="600">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="col-lg-4">
                <div class="card mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-shield-alt gradient-text me-2"></i>
                            <span class="text-label">Security Settings</span>
                        </h5>

                        <!-- Password Change -->
                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="400">
                                <label for="current_password" class="form-label">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                            </div>

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="500">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="600">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="d-grid" data-aos="fade-up" data-aos-delay="700">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card" data-aos="fade-up" data-aos-delay="800">
                    <div class="card-body p-4">
                        <h5 class="card-title text-danger mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                        </h5>

                        <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="password" id="delete_password">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-danger" onclick="showDeleteConfirmation()">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent

@push('styles')
<style>
:root {
    --gradient-start: #00BFA6;
    --gradient-end: #0093E9;
    --gradient-mix: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
    --shadow-color: rgba(0, 191, 166, 0.15);
}

/* Content Layout Spacing */
.content-header {
    margin-bottom: 2rem;
}

.content-title {
    margin-bottom: 0.5rem;
}

.content-subtitle {
    margin-bottom: 2rem;
    color: #6B7280;
}

.gradient-text {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
}

.dashboard-link {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: var(--gradient-start);
    text-decoration: none;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 191, 166, 0.1);
    box-shadow: 0 4px 15px var(--shadow-color);
}

.dashboard-link:hover {
    transform: translateY(-2px);
    background: white;
    box-shadow: 0 8px 25px var(--shadow-color);
    border-color: var(--gradient-start);
}

.dashboard-link i {
    font-size: 1.25rem;
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 1.5rem;
    box-shadow: 0 8px 30px var(--shadow-color);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px var(--shadow-color);
}

.app-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto;
    background: var(--gradient-mix);
    border-radius: 50%;
    padding: 4px;
    position: relative;
}

.app-icon img {
    border: 4px solid white;
    border-radius: 50%;
}

.app-icon::after {
    content: '';
    position: absolute;
    inset: -10px;
    background: var(--gradient-mix);
    filter: blur(20px);
    opacity: 0.3;
    border-radius: 50%;
    z-index: -1;
}

.input-group {
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
    border: 1px solid rgba(0, 191, 166, 0.1);
}

.input-group-text {
    background: transparent;
    border: none;
    color: var(--gradient-start);
    padding: 0.75rem 1rem;
}

.form-control {
    border: none;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    background: transparent;
}

.form-control:focus {
    box-shadow: none;
    background: white;
}

.input-group:focus-within {
    box-shadow: 0 4px 15px var(--shadow-color);
    border-color: var(--gradient-start);
}

.btn-primary {
    background: var(--gradient-mix);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-color);
}

.btn-outline-danger {
    border: 2px solid #E74C3C;
    font-weight: 600;
    border-radius: 1rem;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(231, 76, 60, 0.25);
}

/* Floating decoration */
.card::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: var(--gradient-mix);
    opacity: 0.05;
    transform: rotate(-5deg) scale(1.2);
    z-index: 0;
    transition: all 0.5s ease;
}

.card:hover::before {
    transform: rotate(-8deg) scale(1.3);
    opacity: 0.08;
}

/* Profile Avatar Styles */
.profile-avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 1.5rem;
}

.profile-user-img {
    border: 3px solid #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
}

.profile-avatar-upload {
    position: absolute;
    bottom: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 50%;
    padding: 0.5rem;
    box-shadow: 0 4px 15px rgba(0, 191, 166, 0.15);
    cursor: pointer;
    margin-right: -5px;
    margin-bottom: -5px;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(0, 191, 166, 0.1);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.profile-avatar-upload:hover {
    transform: scale(1.1) rotate(5deg);
    background: white;
    box-shadow: 0 8px 25px rgba(0, 191, 166, 0.25);
    border-color: rgba(0, 191, 166, 0.2);
}

.profile-avatar-upload i {
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.profile-avatar-upload:hover i {
    transform: scale(1.1);
}

.profile-username {
    font-weight: 600;
    color: #2d3748;
}

/* Disabled Input Styling */
.disabled-input {
    background: rgba(0, 191, 166, 0.05);
    border: 1px solid rgba(0, 191, 166, 0.1);
    border-radius: 1rem;
}

.disabled-input .input-group-text {
    background: transparent;
    border: none;
}

.disabled-input .form-control {
    background: transparent;
    color: #2d3748;
    font-weight: 500;
    border: none;
}

.disabled-input .form-control:disabled {
    opacity: 1;
}

/* Themed Input Styling */
.themed-input {
    background: white;
    border: 1px solid rgba(0, 191, 166, 0.1);
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.themed-input .input-group-text {
    background: transparent;
    border: none;
    color: #6B7280;
}

.themed-input .form-control {
    background: transparent;
    border: none;
    color: #2d3748;
    font-weight: 500;
}

.themed-input:focus-within {
    border-color: rgba(0, 191, 166, 0.4);
    box-shadow: 0 4px 15px rgba(0, 191, 166, 0.15);
}

.themed-input:hover {
    border-color: rgba(0, 191, 166, 0.2);
    box-shadow: 0 2px 8px rgba(0, 191, 166, 0.1);
}

.themed-input .form-control:focus {
    box-shadow: none;
}

/* Telegram Input */
.themed-input .fab.fa-telegram {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Label Styling */
.form-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label i {
    font-size: 1rem;
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.form-label .text-label {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Card Title Styling */
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i {
    font-size: 1.2rem;
}

.card-title .text-label {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Security Section Styling */
.security-heading {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.security-heading i {
    font-size: 1rem;
}

.security-heading .text-label {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.security-section {
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(0, 191, 166, 0.1);
}

.security-section:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.security-description {
    color: #6B7280;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

/* Page Title Styling */
.content-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.content-title .gradient-text {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.content-subtitle {
    font-size: 1rem;
    color: #6B7280;
    margin-bottom: 2rem;
}

.content-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-mix);
    border-radius: 12px;
    margin-right: 1rem;
    box-shadow: 0 4px 15px rgba(0, 191, 166, 0.15);
}

.content-icon i {
    font-size: 1.5rem;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

function showDeleteConfirmation() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
}

function submitDeleteAccount() {
    const password = document.getElementById('confirm_password').value;
    const errorDiv = document.getElementById('password-error');
    
    if (!password) {
        errorDiv.textContent = 'Password is required';
        document.getElementById('confirm_password').classList.add('is-invalid');
        return;
    }

    // Set the password to the hidden input
    document.getElementById('delete_password').value = password;
    
    // Submit the form
    document.getElementById('deleteAccountForm').submit();
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
@endsection 