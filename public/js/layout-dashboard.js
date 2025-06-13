// Initialize AOS
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        offset: 20,
        once: true
    });

    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    }

    if (sidebarToggle && sidebarOverlay) {
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }

    // Close sidebar on window resize if screen becomes large
    window.addEventListener('resize', () => {
        if (window.innerWidth > 991.98) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });
}); 