@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Profile Settings</h5>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                                         class="rounded-circle" 
                                         alt="Profile Picture"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                    <label for="avatar" class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm" style="cursor: pointer;">
                                        <i class="fas fa-camera text-primary"></i>
                                    </label>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                                    <p class="text-muted mb-0">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', Auth::user()->name) }}" required autocomplete="name">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" class="form-control bg-light" 
                                       value="{{ Auth::user()->email }}" readonly disabled>
                            </div>
                            <small class="text-muted">Email address cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="telegram_chat_id" class="form-label">Telegram Chat ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-telegram"></i></span>
                                <input id="telegram_chat_id" type="text" class="form-control @error('telegram_chat_id') is-invalid @enderror" 
                                       name="telegram_chat_id" value="{{ old('telegram_chat_id', Auth::user()->telegram_chat_id) }}"
                                       placeholder="Enter your Telegram Chat ID">
                                @error('telegram_chat_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="text-muted">
                                To get your Telegram Chat ID, message our bot at 
                                <a href="https://t.me/your_bot_username" target="_blank">@your_bot_username</a> 
                                and send the command /start
                            </small>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security Settings</h5>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h6>Two-Factor Authentication</h6>
                        <p class="text-muted mb-3">Add additional security to your account using two-factor authentication.</p>
                        
                        @if(Auth::user()->two_factor_enabled)
                            <button class="btn btn-danger" onclick="disableTwoFactor()">
                                <i class="fas fa-times me-1"></i> Disable 2FA
                            </button>
                        @else
                            <button class="btn btn-success" onclick="enableTwoFactor()">
                                <i class="fas fa-shield-alt me-1"></i> Enable 2FA
                            </button>
                        @endif
                    </div>

                    <div>
                        <h6>Active Sessions</h6>
                        <p class="text-muted mb-3">Manage and log out your active sessions on other browsers and devices.</p>
                        <button class="btn btn-outline-danger" onclick="logoutOtherDevices()">
                            <i class="fas fa-sign-out-alt me-1"></i> Log Out Other Devices
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" class="mb-4">
                        @csrf
                        @method('PUT')
                        
                        <h6>Change Password</h6>
                        <p class="text-muted mb-3">Make sure to use a strong, unique password that you don't use elsewhere.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       name="current_password" autocomplete="current-password">
                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input id="password_confirmation" type="password" class="form-control" 
                                       name="password_confirmation" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-key me-1"></i> Update Password
                            </button>
                        </div>
                    </form>

                    <hr>

                    <h6>Delete Account</h6>
                    <p class="text-muted mb-3">Once you delete your account, there is no going back. Please be certain.</p>
                    <button class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                        <i class="fas fa-trash-alt me-1"></i> Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border-radius: 10px;
    border: none;
}
.card-header {
    border-top-left-radius: 10px !important;
    border-top-right-radius: 10px !important;
    border-bottom: 1px solid rgba(0,0,0,.125);
}
.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}
.form-control {
    border-left: none;
}
.input-group:focus-within .input-group-text {
    border-color: #3b82f6;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

function enableTwoFactor() {
    // Implement 2FA setup logic
    alert('Two-factor authentication setup will be implemented here');
}

function disableTwoFactor() {
    if (confirm('Are you sure you want to disable two-factor authentication?')) {
        // Implement 2FA disable logic
        alert('Two-factor authentication will be disabled');
    }
}

function logoutOtherDevices() {
    if (confirm('Are you sure you want to log out all other devices?')) {
        // Implement logout logic
        alert('Other devices will be logged out');
    }
}

function confirmDeleteAccount() {
    if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
        // Implement account deletion logic
        alert('Account deletion will be implemented');
    }
}
</script>
@endpush
@endsection 