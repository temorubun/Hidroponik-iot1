@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Profile Settings</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="{{ Auth::user()->avatar_url }}" 
                                     alt="Profile Picture" 
                                     class="rounded-circle mb-3"
                                     id="avatar-preview"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <label for="avatar" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle" style="width: 32px; height: 32px;">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                </label>
                            </div>

                            @error('avatar')
                                <span class="text-danger d-block small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', Auth::user()->name) }}" required autocomplete="name">
                                
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', Auth::user()->email) }}" required autocomplete="email">
                                
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Telegram Chat ID Field -->
                        <div class="mb-3">
                            <label for="telegram_chat_id" class="form-label">
                                Telegram Chat ID
                                <i class="fas fa-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   title="Find your Chat ID by messaging @get_id_bot on Telegram"></i>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-telegram"></i></span>
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

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
@endsection 