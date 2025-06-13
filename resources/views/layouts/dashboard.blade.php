<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('meta')

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">

    <title>Monitor - Hidroponik IoT</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/floating-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layout-dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Floating Background -->
        <div class="floating-bg"></div>

        @include('components.floating-icons')

        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggler" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <a href="{{ url('/') }}" class="sidebar-brand">
                <i class="fas fa-leaf"></i>
                Hidroponik IoT
            </a>
            
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('projects.index') }}" class="sidebar-nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="fas fa-project-diagram"></i>
                        Projects
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('devices.index') }}" class="sidebar-nav-link {{ request()->routeIs('devices.*') ? 'active' : '' }}">
                        <i class="fas fa-microchip"></i>
                        Devices
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('pins.index') }}" class="sidebar-nav-link {{ request()->routeIs('pins.*') ? 'active' : '' }}">
                        <i class="fas fa-plug"></i>
                        Pins
                    </a>
                </li>
                <li class="sidebar-nav-item mt-auto">
                    <a href="{{ route('profile.edit') }}" class="sidebar-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user-circle"></i>
                        Profile
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </aside>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="{{ asset('js/layout-dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html> 