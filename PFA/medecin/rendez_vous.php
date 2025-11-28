<?php
session_start();

// Vérifier si le patient est connecté
if (!isset($_SESSION['patient'])) {
    header('Location: login_patient.php');
    exit;
}

// Récupérer les infos du patient
$id_patient = $_SESSION['patient']['id'];
$nom = $_SESSION['patient']['nom'];
$prenom = $_SESSION['patient']['prenom'];

// Connexion à la base de données
require_once 'connecte_bd.php';

// Récupérer la liste des médecins
$medecins = [];
$sql = "SELECT id, nom, prenom, specialite FROM medecin";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medecins[] = $row;
    }
} else {
    $error = "Erreur lors de la récupération des médecins.";
}

// Traitement du formulaire de rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medecin = $_POST['id_medecin'] ?? null;
    $date_rdv = $_POST['date_rdv'] ?? null;
    $heure_rdv = $_POST['heure_rdv'] ?? null;
    $motif = $_POST['motif'] ?? '';

    if ($id_medecin && $date_rdv && $heure_rdv) {
        $stmt = $conn->prepare("INSERT INTO rendez_vous (id_patient, id_medecin, date_rdv, heure_rdv, motif) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iisss", $id_patient, $id_medecin, $date_rdv, $heure_rdv, $motif);
            if ($stmt->execute()) {
                $success = "Votre rendez-vous a été enregistré avec succès !";
            } else {
                $error = "Erreur lors de l'enregistrement du rendez-vous : " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Erreur de préparation de la requête : " . $conn->error;
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre Rendez-vous - DentalCare</title>
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
            --footer-text: #fff;
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
        
        .card {
            background-color: var(--card-bg);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.125);
        }
        
        .dark-theme .card {
            border-color: #333;
        }
        
        .dark-theme .card-header {
            border-bottom-color: #333;
        }
        
        .card-medecin {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            cursor: pointer;
            background-color: var(--card-bg);
        }
        
        .card-medecin:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .card-medecin.selected {
            border: 2px solid var(--primary);
            background-color: var(--primary-light);
        }
        
        .medecin-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-light);
        }
        
        .specialite-badge {
            background-color: var(--primary);
            color: white;
        }
        
        .rdv-form-container {
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .time-slot {
            border: 1px solid var(--secondary);
            border-radius: 5px;
            padding: 8px 12px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: var(--light);
        }
        
        .dark-theme .time-slot {
            background-color: var(--light);
            color: var(--text-color);
            border-color: #555;
        }
        
        .time-slot:hover {
            background-color: var(--primary-light);
        }
        
        .time-slot.selected {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
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
        
        /* Styles pour les formulaires en mode sombre */
        .dark-theme .form-control {
            background-color: var(--light);
            color: var(--text-color);
            border-color: #555;
        }
        
        .dark-theme .form-control:focus {
            background-color: var(--light);
            color: var(--text-color);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(33, 150, 243, 0.25);
        }
        
        .dark-theme .form-label {
            color: var(--text-color);
        }
        
        /* Style pour le sélecteur de date en mode sombre */
        .dark-theme input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        
        .dark-theme .list-group-item {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: #333;
        }
        
        .dark-theme .list-group-item.active {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .dark-theme .text-muted {
            color: #b0b0b0 !important;
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home_patient.php">Dental<span>Care.</span></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPatient">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPatient">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home_patient.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="rendez_vous.php">Prendre RDV</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_rendez_vous.php">Mes RDV</a>
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

    <!-- Contenu principal -->
    <main class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="text-center mb-4">Prendre un rendez-vous</h1>
                    <p class="lead text-center">Choisissez un médecin et sélectionnez une date et heure disponible</p>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Liste des médecins -->
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Nos médecins</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($medecins as $medecin): ?>
                                    <a href="#" class="list-group-item list-group-item-action medecin-item" 
                                       data-id="<?php echo $medecin['id']; ?>">
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($medecin['prenom'] . ' ' . $medecin['nom']); ?>&background=random" 
                                                 class="medecin-img me-3" alt="Dr. <?php echo $medecin['prenom']; ?>">
                                            <div>
                                                <h6 class="mb-1">Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?></h6>
                                                <span class="badge specialite-badge"><?php echo htmlspecialchars($medecin['specialite']); ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de rendez-vous -->
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Détails du rendez-vous</h5>
                        </div>
                        <div class="card-body">
                            <form id="rdvForm" method="POST" action="rendez_vous.php">
                                <input type="hidden" id="id_medecin" name="id_medecin" required>
                                
                                <div class="mb-3">
                                    <label for="date_rdv" class="form-label">Date du rendez-vous</label>
                                    <input type="date" class="form-control" id="date_rdv" name="date_rdv" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Heure du rendez-vous</label>
                                    <div class="time-slots d-flex flex-wrap">
                                        <?php 
                                        // Créer des créneaux horaires de 9h à 17h toutes les 30 minutes
                                        $start = strtotime('09:00');
                                        $end = strtotime('17:00');
                                        for ($i = $start; $i <= $end; $i += 1800) {
                                            $time = date('H:i', $i);
                                            echo '<div class="time-slot" data-time="' . $time . '">' . $time . '</div>';
                                        }
                                        ?>
                                    </div>
                                    <input type="hidden" id="heure_rdv" name="heure_rdv" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="motif" class="form-label">Motif de la consultation (optionnel)</label>
                                    <textarea class="form-control" id="motif" name="motif" rows="3"></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calendar-check me-2"></i>Confirmer le rendez-vous
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
        // Sélection du médecin
        document.querySelectorAll('.medecin-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Retirer la sélection précédente
                document.querySelectorAll('.medecin-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // Ajouter la sélection actuelle
                this.classList.add('active');
                
                // Mettre à jour l'ID du médecin dans le formulaire
                const medecinId = this.getAttribute('data-id');
                document.getElementById('id_medecin').value = medecinId;
            });
        });
        
        // Sélection du créneau horaire
        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.addEventListener('click', function() {
                // Retirer la sélection précédente
                document.querySelectorAll('.time-slot').forEach(s => {
                    s.classList.remove('selected');
                });
                
                // Ajouter la sélection actuelle
                this.classList.add('selected');
                
                // Mettre à jour l'heure dans le formulaire
                const time = this.getAttribute('data-time');
                document.getElementById('heure_rdv').value = time;
            });
        });
        
        // Validation du formulaire avant soumission
        document.getElementById('rdvForm').addEventListener('submit', function(e) {
            if (!document.getElementById('id_medecin').value) {
                e.preventDefault();
                alert('Veuillez sélectionner un médecin');
                return false;
            }
            
            if (!document.getElementById('date_rdv').value) {
                e.preventDefault();
                alert('Veuillez sélectionner une date');
                return false;
            }
            
            if (!document.getElementById('heure_rdv').value) {
                e.preventDefault();
                alert('Veuillez sélectionner une heure');
                return false;
            }
        });
        
        // Gestion des thèmes
        function setDarkTheme() {
            document.body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
            updateFormStyles();
        }
        
        function setLightTheme() {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
            updateFormStyles();
        }
        
        // Mise à jour des styles des éléments de formulaire
        function updateFormStyles() {
            const isDark = document.body.classList.contains('dark-theme');
            const inputs = document.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                if (isDark) {
                    input.style.backgroundColor = '#1E1E1E';
                    input.style.color = '#f5f5f5';
                    input.style.borderColor = '#555';
                } else {
                    input.style.backgroundColor = '';
                    input.style.color = '';
                    input.style.borderColor = '';
                }
            });
        }
        
        // Vérifier le thème sauvegardé au chargement
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('theme') === 'dark') {
                setDarkTheme();
            } else {
                setLightTheme();
            }
            
            // Appliquer les styles initiaux
            updateFormStyles();
        });
    </script>
</body>
</html>