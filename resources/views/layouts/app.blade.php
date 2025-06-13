<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('meta')
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/floating-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layout-app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/notifications.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="app" class="main-container">
        @include('components.floating-icons')
        
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid px-4">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <i class="fas fa-microchip me-2"></i>{{ config('app.name', 'IoT Blink') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <img src="{{ Auth::user()->avatar_url }}" alt="Profile Picture" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user-circle me-1"></i>Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-1"></i>{{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="content-container">
            @auth
            <aside class="sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('projects*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                            <i class="fas fa-project-diagram"></i>Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('devices*') ? 'active' : '' }}" href="{{ route('devices.index') }}">
                            <i class="fas fa-microchip"></i>Devices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pins*') ? 'active' : '' }}" href="{{ route('pins.index') }}">
                            <i class="fas fa-plug"></i>Pins
                        </a>
                    </li>
                </ul>
            </aside>
            @endauth

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @stack('scripts')
</body>
</html> 