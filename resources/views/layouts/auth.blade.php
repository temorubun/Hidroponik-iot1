<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">


    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layout-auth.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    <link href="{{ asset('css/form-elements.css') }}" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Floating Background -->
        <div class="floating-bg"></div>
        
        <!-- Floating Icons -->
        @include('components.floating-icons')
        
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html> 