<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menuntut Ilmu - Dashboard</title>
    <!-- Link ke Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link ke CSS Kustom -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> Jadwal KBM
            </a>
            <!-- Toggle button for responsive navbar -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Dashboard Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelas.php">
                            <i class="fas fa-calendar-alt me-2"></i>Lihat Jadwal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="crud_jadwal.php">
                            <i class="fas fa-edit me-2"></i>Kelola Jadwal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="batas_ajar.php">
                            <i class="fas fa-bookmark me-2"></i>Batas Ajar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistik.php">
                            <i class="fas fa-chart-bar me-2"></i>Statistik
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Script JS untuk Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script untuk active navigation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get current page filename
            const currentPage = window.location.pathname.split('/').pop();
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to current page link
            const navLinks = {
                'index.php': 'index.php',
                'kelas.php': 'kelas.php',
                'crud_jadwal.php': 'crud_jadwal.php',
                'batas_ajar.php': 'batas_ajar.php',
                'statistik.php': 'statistik.php'
            };
            
            if (navLinks[currentPage]) {
                const activeLink = document.querySelector(`a[href="${navLinks[currentPage]}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
            
            // Add smooth scroll effect for navbar brand
             const navbarBrand = document.querySelector('.navbar-brand');
             if (navbarBrand) {
                 navbarBrand.addEventListener('click', function(e) {
                     if (this.getAttribute('href') === 'index.php' && currentPage === 'index.php') {
                         e.preventDefault();
                         window.scrollTo({
                             top: 0,
                             behavior: 'smooth'
                         });
                     }
                 });
             }
             
             // Add scroll effect to navbar
             const navbar = document.querySelector('.navbar');
             let lastScrollTop = 0;
             
             window.addEventListener('scroll', function() {
                 const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                 
                 if (scrollTop > 50) {
                     navbar.classList.add('scrolled');
                 } else {
                     navbar.classList.remove('scrolled');
                 }
                 
                 lastScrollTop = scrollTop;
             });
             
             // Add loading effect to navigation links
             document.querySelectorAll('.nav-link').forEach(link => {
                 link.addEventListener('click', function(e) {
                     // Don't add loading if it's the current page or an anchor link
                     const href = this.getAttribute('href');
                     if (href && !href.startsWith('#') && href !== currentPage) {
                         this.classList.add('nav-loading');
                         
                         // Remove loading class after a short delay if navigation fails
                         setTimeout(() => {
                             this.classList.remove('nav-loading');
                         }, 3000);
                     }
                 });
             });
             
             // Add hover sound effect (optional)
             document.querySelectorAll('.nav-link, .navbar-brand').forEach(element => {
                 element.addEventListener('mouseenter', function() {
                     // Add subtle vibration effect on supported devices
                     if (navigator.vibrate) {
                         navigator.vibrate(10);
                     }
                 });
             });
         });
    </script>