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

        <!-- Profile Information -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100">
                <div class="card-body box-profile text-center">
                    <div class="profile-avatar-wrapper">
                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                             class="profile-user-img img-fluid rounded-circle" 
                             alt="Profile Picture"
                             style="width: 100px; height: 100px; object-fit: cover;">
                        <label for="avatar" class="profile-avatar-upload">
                            <i class="fas fa-camera gradient-text"></i>
                        </label>
                        <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                    </div>
                    <h3 class="profile-username h4 mb-2">{{ Auth::user()->name }}</h3>
                    <p class="text-muted mb-3">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
                    <hr>
                    <div class="mt-3">
                        <div class="profile-info-item">
                            <label class="form-label mb-0">
                                <i class="fas fa-envelope gradient-icon"></i>
                                <span class="gradient-text">Email</span>
                            </label>
                            <div class="input-group disabled-input">
                                <span class="input-group-text"><i class="fas fa-envelope gradient-text"></i></span>
                                <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly disabled>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <label class="form-label mb-0">
                                <i class="fas fa-circle gradient-icon"></i>
                                <span class="gradient-text">Status</span>
                            </label>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Personal Information -->
            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title m-0">
                        <i class="fas fa-user me-2 gradient-icon"></i>
                        <span class="gradient-text">Personal Information</span>
                    </h3>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="form-section">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-4">
                            <label for="name" class="form-label">
                                <i class="fas fa-user gradient-icon"></i>
                                <span class="gradient-text">Name</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope gradient-icon"></i>
                                <span class="gradient-text">Email Address</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" class="form-control bg-light" value="{{ Auth::user()->email }}" readonly disabled>
                            </div>
                            <small class="text-muted mt-1">Email address cannot be changed</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="telegram_chat_id" class="form-label">
                                <i class="fab fa-telegram gradient-icon"></i>
                                <span class="gradient-text">Telegram Chat ID</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fab fa-telegram"></i></span>
                                <input id="telegram_chat_id" type="text" class="form-control @error('telegram_chat_id') is-invalid @enderror" 
                                       name="telegram_chat_id" value="{{ old('telegram_chat_id', Auth::user()->telegram_chat_id) }}"
                                       placeholder="Enter your Telegram Chat ID">
                                @error('telegram_chat_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="text-muted mt-1">
                                To get your Telegram Chat ID, message our bot at 
                                <a href="https://t.me/your_bot_username" target="_blank" class="text-decoration-none">@your_bot_username</a> 
                                and send the command /start
                            </small>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-action">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card shadow-sm security-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title m-0">
                        <i class="fas fa-shield-alt me-2 gradient-icon"></i>
                        <span class="gradient-text">Security Settings</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="security-section">
                        <h5 class="security-heading">
                            <i class="fas fa-shield-alt me-2 gradient-icon"></i>
                            <span class="gradient-text">Two-Factor Authentication</span>
                        </h5>
                        <p class="security-description">Add additional security to your account using two-factor authentication.</p>
                        
                        @if(Auth::user()->two_factor_enabled)
                            <button class="btn btn-danger btn-action" onclick="disableTwoFactor()">
                                <i class="fas fa-times me-2"></i>Disable 2FA
                            </button>
                        @else
                            <button class="btn btn-success btn-action" onclick="enableTwoFactor()">
                                <i class="fas fa-shield-alt me-2"></i>Enable 2FA
                            </button>
                        @endif
                    </div>

                    <div class="security-section">
                        <h5 class="security-heading">
                            <i class="fas fa-desktop me-2 gradient-icon"></i>
                            <span class="gradient-text">Active Sessions</span>
                        </h5>
                        <p class="security-description">Manage and log out your active sessions on other browsers and devices.</p>
                        <button class="btn btn-warning btn-action" onclick="logoutOtherDevices()">
                            <i class="fas fa-sign-out-alt me-2"></i>Log Out Other Devices
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="security-section">
                        <h5 class="security-heading">
                            <i class="fas fa-key me-2 gradient-icon"></i>
                            <span class="gradient-text">Change Password</span>
                        </h5>
                        <p class="security-description">Make sure to use a strong, unique password that you don't use elsewhere.</p>

                        <form method="POST" action="{{ route('profile.password.update') }}" class="security-section">
                            @csrf
                            @method('PUT')

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="400">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock gradient-icon"></i>
                                    <span class="gradient-text">Current Password</span>
                                </label>
                                <div class="input-group themed-input">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                            </div>

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="500">
                                <label for="password" class="form-label">
                                    <i class="fas fa-key gradient-icon"></i>
                                    <span class="gradient-text">New Password</span>
                                </label>
                                <div class="input-group themed-input">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="mb-4" data-aos="fade-up" data-aos-delay="600">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-check-double gradient-icon"></i>
                                    <span class="gradient-text">Confirm Password</span>
                                </label>
                                <div class="input-group themed-input">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-danger btn-action">
                                    <i class="fas fa-key me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="my-4">

                    <div class="security-section" data-aos="fade-up" data-aos-delay="400">
                        <h5 class="security-heading">Delete Account</h5>
                        <p class="security-description">Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-danger btn-action" onclick="confirmDeleteAccount()">
                            <i class="fas fa-trash-alt me-2"></i>Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/profile.css') }}" rel="stylesheet">
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

.gradient-text {
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

.profile-info-item {
    margin-bottom: 1rem;
}

.profile-info-item:last-child {
    margin-bottom: 0;
}

.form-label .text-label {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
@endpush

@push('scripts')
<script src="{{ asset('js/profile.js') }}"></script>
@endpush 