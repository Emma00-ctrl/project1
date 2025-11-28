<?php
session_start();

// Vérifier si le patient est connecté
if (!isset($_SESSION['patient'])) {
    header('Location: index.html');
    exit;
}

// Récupérer les infos du patient
$nom = $_SESSION['patient']['nom'];
$prenom = $_SESSION['patient']['prenom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Patient - DentalCare</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1976D2;
            --primary-light: #E3F2FD;
            --primary-dark: #0D47A1;
            --secondary: #f4f4f4;
            --light: #fff;
            --dark: #212121;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
            --text-color: #212121;
            --bg-color: #f4f4f4;
            --card-bg: #fff;
            --footer-bg: #212121;
            --footer-text: #ffffff;
        }
        
        .dark-theme {
            --primary: #2196F3;
            --primary-light: #424242;
            --primary-dark: #90CAF9;
            --secondary: #121212;
            --light: #1E1E1E;
            --dark: #f5f5f5;
            --text-color: #f5f5f5;
            --bg-color: #121212;
            --card-bg: #1E1E1E;
            --footer-bg: #000000;
            --footer-text: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-brand span {
            color: var(--primary);
        }
        
        .welcome-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 6rem 0;
            text-align: center;
        }
        
        .card-service {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
        }
        
        .card-service:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .service-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .quick-actions .btn-action {
            border-radius: 50px;
            padding: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        
        footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
            transition: all 0.3s ease;
        }
        
        .social-icon {
            color: var(--footer-text);
            font-size: 1.2rem;
            margin-right: 1rem;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            color: var(--primary);
        }
        
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
        }
        
        .profile-header {
            height: 150px;
            background: var(--primary);
            position: relative;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-dark);
        }
        
        .bg-primary-light {
            background-color: var(--primary-light);
        }
        
        .text-primary-dark {
            color: var(--primary-dark);
        }
        
        .nav-link.active {
            font-weight: 600;
            color: var(--primary) !important;
            border-bottom: 2px solid var(--primary);
        }
        
        .theme-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            margin-left: 10px;
            transition: transform 0.3s;
        }
        
        .theme-btn:hover {
            transform: scale(1.1);
        }
        
        .light-theme-btn {
            background-color: #f4f4f4;
            color: #212121;
        }
        
        .dark-theme-btn {
            background-color: #212121;
            color: #f4f4f4;
        }
        
        .theme-buttons {
            display: flex;
            align-items: center;
            margin-left: 15px;
        }
        
        .bg-white {
            background-color: var(--card-bg) !important;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .card {
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        /* Styles spécifiques pour le mode sombre */
        .dark-theme .text-muted {
            color: #b0b0b0 !important;
        }
        
        .dark-theme .card {
            border-color: #333;
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">Dental<span>Care.</span></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPatient">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPatient">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#appointments">Rendez-vous</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="theme-buttons">
                        <button class="theme-btn light-theme-btn" title="Thème clair" onclick="setLightTheme()">
                            <i class="fas fa-sun"></i>
                        </button>
                        <button class="theme-btn dark-theme-btn" title="Thème sombre" onclick="setDarkTheme()">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                    
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($prenom . ' ' . $nom); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="mes_rendez_vous.php"><i class="fas fa-calendar-check me-2"></i>Mes rendez-vous</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit me-2"></i>Mon profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout_patient.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Section Bienvenue -->
    <section class="welcome-section" id="home">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Bonjour <?php echo htmlspecialchars($prenom); ?> !</h1>
            <p class="lead mb-5">Bienvenue dans votre espace patient DentalCare</p>
            <a href="rendez_vous.php" class="btn btn-primary btn-lg px-4 me-2">
                <i class="fas fa-calendar-plus me-2"></i>Prendre rendez-vous
            </a>
            <a href="mes_rendez_vous.php" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-list me-2"></i>Vos rendez-vous
            </a>
        </div>
    </section>

    <!-- Section Profil -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="profile-card bg-white">
                        <div class="profile-header">
                        <div class="profile-img">
                            <i class="fas fa-user"></i>
                        </div>
                        </div>
                        
                        <div class="p-4 text-center mt-5">
                            <h3><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h3>
                            <p class="text-muted">Patient chez DentalCare</p>
                            
                            <div class="d-flex justify-content-center mt-4">
                                <a href="#" class="btn btn-outline-primary me-3">
                                    <i class="fas fa-edit me-2"></i>Modifier profil
                                </a>
                                <a href="rendez_vous.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>Nouveau RDV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Services -->
    <section class="py-5 bg-white" id="services">
        <div class="container">
            <h2 class="text-center mb-5">Nos Services Dentaires</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-tooth"></i>
                            </div>
                            <h4>Soins Dentaires</h4>
                            <p class="text-muted">Soins préventifs et curatifs pour maintenir une bonne santé bucco-dentaire.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-teeth"></i>
                            </div>
                            <h4>Blanchiment</h4>
                            <p class="text-muted">Techniques professionnelles pour un sourire plus blanc et éclatant.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-teeth-open"></i>
                            </div>
                            <h4>Orthodontie</h4>
                            <p class="text-muted">Correction de l'alignement dentaire pour un sourire harmonieux.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-toothbrush"></i>
                            </div>
                            <h4>Hygiène Dentaire</h4>
                            <p class="text-muted">Nettoyage professionnel et conseils pour une hygiène optimale.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-teeth"></i>
                            </div>
                            <h4>Implants Dentaires</h4>
                            <p class="text-muted">Solutions durables pour remplacer les dents manquantes.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-service h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-comment-medical"></i>
                            </div>
                            <h4>Consultation en Ligne</h4>
                            <p class="text-muted">Conseils dentaires à distance avec nos spécialistes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Rendez-vous -->
    <section class="py-5 bg-primary-light" id="appointments">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="text-primary-dark">Vos prochains rendez-vous</h2>
                    <p class="lead">Gérez facilement vos rendez-vous depuis votre espace personnel.</p>
                    <p>Consultez vos rendez-vous à venir, modifiez-les ou annulez-les si nécessaire. Vous pouvez également prendre de nouveaux rendez-vous en ligne.</p>
                    
                    <div class="d-flex mt-4">
                        <a href="mes_rendez_vous.php" class="btn btn-primary me-3">
                            <i class="fas fa-calendar-alt me-2"></i>Voir mes RDV
                        </a>
                        <a href="rendez_vous.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Nouveau RDV
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Prochain rendez-vous</h5>
                            
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                    <i class="fas fa-calendar-day text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Consultation de contrôle</h6>
                                    <small class="text-muted">15 Juin 2023 - 14:30</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                    <i class="fas fa-user-md text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Dr. Sophie Martin</h6>
                                    <small class="text-muted">Dentiste généraliste</small>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <a href="mes_rendez_vous.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>Voir tous les rendez-vous
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Actions rapides -->
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5">Actions rapides</h2>
            
            <div class="row quick-actions g-4">
                <div class="col-md-4">
                    <a href="rendez_vous.php" class="btn btn-action btn-primary w-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <i class="fas fa-calendar-plus mb-3 fs-2"></i>
                        <span>Prendre rendez-vous</span>
                    </a>
                </div>
                
                <div class="col-md-4">
                    <a href="mes_rendez_vous.php" class="btn btn-action btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <i class="fas fa-calendar-check mb-3 fs-2"></i>
                        <span>Mes rendez-vous</span>
                    </a>
                </div>
                
                <div class="col-md-4">
                    <a href="#" class="btn btn-action btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <i class="fas fa-file-medical mb-3 fs-2"></i>
                        <span>Mes documents</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">

                <!-- Contact Info -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Contactez-nous</h5>
                    <p><i class="fas fa-envelope me-3"></i> contact@dentalcare.com</p>
                    <p><i class="fas fa-phone me-3"></i> +216 20 123 456</p>
                </div>

                <!-- Adresse -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Adresse</h5>
                    <p><i class="fas fa-map-marker-alt me-3"></i> 123 Rue de la Santé, Tunis, Tunisie</p>
                </div>

                <!-- Horaires -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Horaires</h5>
                    <p><i class="fas fa-clock me-3"></i> Lundi - Vendredi: 8h - 18h</p>
                    <p><i class="fas fa-clock me-3"></i> Samedi: 9h - 13h</p>
                    <p><i class="fas fa-times-circle me-3"></i> Dimanche: Fermé</p>
                </div>

                <!-- Réseaux sociaux -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Suivez-nous 🦷</h5>
                    <a href="#" class="me-4 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="me-4 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="me-4 social-icon"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="me-4 social-icon"><i class="fab fa-twitter"></i></a>
                </div>

            </div>

            <hr class="my-4">

            <!-- Copyright -->
            <div class="row text-center">
                <div class="col-md-12">
                    <p class="mb-0">&copy; 2023 DentalCare. Votre sourire, notre priorité <br>Réalisé par Emna BenHarb (G1) et Ameni Nagati (G2)</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activer les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Gestion des thèmes
        function setDarkTheme() {
            document.body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
        }
        
        function setLightTheme() {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
        }
        
        // Vérifier le thème sauvegardé
        if (localStorage.getItem('theme') === 'dark') {
            setDarkTheme();
        } else {
            setLightTheme();
        }
    </script>
</body>
</html>