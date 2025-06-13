@extends('layouts.auth')

@section('title', 'Login - Hidroponik IoT')

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
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card" data-aos="fade-up" data-aos-duration="1000">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}" class="app-icon-link">
                            <div class="app-icon mb-4" data-aos="zoom-in" data-aos-delay="200">
                                <i class="fas fa-leaf fa-3x"></i>
                            </div>
                            <h2 class="gradient-text" data-aos="fade-up" data-aos-delay="400">Welcome Back!</h2>
                        </a>
                        <p class="text-muted" data-aos="fade-up" data-aos-delay="500">Sign in to continue to your dashboard</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="form-group" data-aos="fade-up" data-aos-delay="600">
                            <label for="email" class="form-label gradient-text">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input id="email" type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" 
                                    required autocomplete="email" autofocus
                                    placeholder="Enter your email">
                            </div>
                            @error('email')
                                <div class="invalid-feedback" role="alert">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="form-group" data-aos="fade-up" data-aos-delay="700">
                            <label for="password" class="form-label gradient-text">{{ __('Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input id="password" type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="current-password"
                                    placeholder="Enter your password">
                            </div>
                            @error('password')
                                <div class="invalid-feedback" role="alert">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="form-group" data-aos="fade-up" data-aos-delay="800">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0" data-aos="fade-up" data-aos-delay="900">
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login') }}
                            </button>
                            
                            <a class="btn btn-primary w-100" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-2"></i>{{ __('Need an account? Register') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 