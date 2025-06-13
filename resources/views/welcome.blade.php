<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">

        <title>Sistem Hidroponik IoT</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        
        <!-- AOS Animation -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        
        <!-- Styles -->
        <link href="{{ asset('css/common.css') }}" rel="stylesheet">
        <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
    </head>
    <body>
        <!-- Floating Background -->
        <div class="floating-bg"></div>

        <div class="hero">
            <div class="floating-icons">
                <i class="fas fa-leaf floating-icon" style="top: 15%; left: 10%;"></i>
                <i class="fas fa-tint floating-icon" style="top: 60%; left: 15%; animation-delay: 1s;"></i>
                <i class="fas fa-seedling floating-icon" style="top: 25%; right: 15%; animation-delay: 2s;"></i>
                <i class="fas fa-flask floating-icon" style="top: 70%; right: 10%; animation-delay: 3s;"></i>
                <i class="fas fa-microchip floating-icon" style="top: 40%; left: 20%; animation-delay: 4s;"></i>
            </div>
            <div class="container">
                <h1 data-aos="fade-up">Sistem Monitoring dan<br>Kontrol pH Hidroponik</h1>
                <p data-aos="fade-up" data-aos-delay="100">
                    Pantau dan kendalikan pH tanaman hidroponik Anda secara real-time. 
                    Optimalkan pertumbuhan tanaman dengan sistem monitoring yang akurat 
                    dan kontrol yang presisi.
                </p>
                <div class="d-flex justify-content-center">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary" data-aos="fade-up" data-aos-delay="200">
                        Get Started
                    </a>
                </div>
            </div>
        </div>

        <section class="hero">
            <div class="container">
                <div class="features-header" data-aos="fade-up">
                    <h2>Fitur Unggulan</h2>
                    <p>Nikmati berbagai fitur canggih untuk mengoptimalkan sistem hidroponik Anda</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3 class="gradient-text">
                            <i class="fas fa-tachometer-alt"></i>
                            Monitoring Real
                        </h3>
                        <p>Pantau pH tanaman Anda secara real-time melalui dashboard yang intuitif. Dapatkan notifikasi instan saat terjadi perubahan signifikan.</p>
                    </div>
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                        <i class="fas fa-microchip feature-icon"></i>
                        <h3 class="gradient-text">
                            <i class="fas fa-robot"></i>
                            Kontrol Otomatis
                        </h3>
                        <p>Sistem kontrol otomatis menjaga pH tanaman tetap optimal. Atur parameter sesuai kebutuhan dan biarkan sistem bekerja untuk Anda.</p>
                    </div>
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-project-diagram feature-icon"></i>
                        <h3 class="gradient-text">
                            <i class="fas fa-chart-area"></i>
                            Data Analysis
                        </h3>
                        <p>Dapatkan wawasan mendalam tentang kondisi tanaman Anda melalui data analysis yang komprehensif dan visualisasi yang mudah dipahami.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="hero" data-aos="fade-up">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand mb-4">
                        <i class="fas fa-leaf fa-2x gradient-icon"></i>
                        <h3 class="gradient-text mt-3">Hidroponik IoT</h3>
                        <p class="text-muted">Solusi Monitoring dan Kontrol pH Tanaman Hidroponik</p>
                    </div>
                    
                    <div class="social-links" data-aos="fade-up" data-aos-delay="100">
                        <a href="#" class="social-link" data-aos="fade-up" data-aos-delay="100">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" data-aos="fade-up" data-aos-delay="200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" data-aos="fade-up" data-aos-delay="300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" data-aos="fade-up" data-aos-delay="400">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                    
                    <div class="footer-bottom mt-4">
                        <p class="copyright" data-aos="fade-up" data-aos-delay="500">
                            &copy; {{ date('Y') }} Hidroponik IoT. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AOS.init({
                    duration: 800,
                    once: true,
                    easing: 'ease-out-cubic'
                });
            });
        </script>
    </body>
</html>
