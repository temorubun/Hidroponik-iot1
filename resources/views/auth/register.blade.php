@extends('layouts.auth')
@section('title', 'Register - Hidroponik IoT')

@section('content')
<style>
    .gradient-text {
        background: linear-gradient(45deg, #2193b0, #6dd5ed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: bold;
    }
</style>
<div class="register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card" data-aos="fade-up" data-aos-duration="1000">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}" class="app-icon-link">
                            <div class="app-icon mb-4" data-aos="zoom-in" data-aos-delay="200">
                                <i class="fas fa-leaf fa-3x"></i>
                            </div>
                        </a>
                        <h2 class="gradient-text" data-aos="fade-up" data-aos-delay="400">Create Account</h2>
                        <p class="text-muted" data-aos="fade-up" data-aos-delay="500">Fill in your information to get started</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4" data-aos="fade-up" data-aos-delay="600">
                            <label for="name" class="form-label gradient-text">{{ __('Name') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input id="name" type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    name="name" value="{{ old('name') }}" 
                                    required autocomplete="name" autofocus
                                    placeholder="Enter your name">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4" data-aos="fade-up" data-aos-delay="700">
                            <label for="email" class="form-label gradient-text">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input id="email" type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" 
                                    required autocomplete="email"
                                    placeholder="Enter your email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4" data-aos="fade-up" data-aos-delay="800">
                            <label for="password" class="form-label gradient-text">{{ __('Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input id="password" type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="new-password"
                                    placeholder="Create a password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4" data-aos="fade-up" data-aos-delay="900">
                            <label for="password-confirm" class="form-label gradient-text">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Confirm your password">
                            </div>
                        </div>

                        <div class="form-group" data-aos="fade-up" data-aos-delay="1000">
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>{{ __('Register') }}
                            </button>
                            
                            <a class="btn btn-primary w-100" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ __('Already have an account? Login') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection 