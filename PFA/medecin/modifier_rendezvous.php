<?php
session_start();
require_once 'connecte_bd.php';
// Vérifier si le patient est connecté
if (!isset($_SESSION['patient'])) {
    header('Location: index.html');
    exit;
}

$id_patient = $_SESSION['patient']['id'];
$nom_patient = $_SESSION['patient']['nom'];
$prenom_patient = $_SESSION['patient']['prenom'];

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "medical");
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données: " . $mysqli->connect_error);
}

// Récupérer le RDV à modifier
$id_rdv = $_GET['id'] ?? 0;
$rdv = null;

if ($id_rdv) {
    $query = "SELECT r.*, m.nom AS medecin_nom, m.prenom AS medecin_prenom, m.specialite 
              FROM rendez_vous r
              JOIN medecin m ON r.id_medecin = m.id
              WHERE r.id = ? AND r.id_patient = ?";
              
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id_rdv, $id_patient);
    $stmt->execute();
    $result = $stmt->get_result();
    $rdv = $result->fetch_assoc();
    $stmt->close();
}

// Si le RDV n'existe pas ou n'appartient pas au patient
if (!$rdv) {
    $_SESSION['error_message'] = "Rendez-vous introuvable ou vous n'avez pas les droits pour le modifier.";
    header('Location: mes_rendez_vous.php');
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rdv'])) {
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];
    
    // Mettre le statut à "en attente" après modification
    $update_query = "UPDATE rendez_vous 
                     SET date_rdv = ?, heure_rdv = ?, motif = ?, statut = 'en attente'
                     WHERE id = ? AND id_patient = ?";
                     
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("sssii", $date_rdv, $heure_rdv, $motif, $id_rdv, $id_patient);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Le rendez-vous a été modifié avec succès.";
    } else {
        $_SESSION['error_message'] = "❌ Erreur lors de la modification du rendez-vous.";
    }
    
    $stmt->close();
    $mysqli->close();
    header('Location: mes_rendez_vous.php');
    exit;
}

// Récupérer la liste des médecins (pour affichage)
$medecins_query = "SELECT id, nom, prenom, specialite FROM medecin ORDER BY nom, prenom";
$medecins_result = $mysqli->query($medecins_query);
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Rendez-vous - DentalCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .form-container {
            max-width: 700px;
            margin: 2rem auto;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-brand span {
            color: var(--primary);
        }
        
        /* Styles pour les éléments de formulaire en mode sombre */
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
        
        .dark-theme input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
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
                        <a class="nav-link" href="rendez_vous.php">Prendre RDV</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mes_rendez_vous.php">Mes RDV</a>
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
                                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($prenom_patient . ' ' . $nom_patient) ?>
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
            <div class="form-container">
                <h2 class="mb-4">
                    <span class="d-inline-flex align-items-center">
                        <i class="fas fa-edit me-3" style="background: linear-gradient(135deg, #17a2b8, #6f42c1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span style="background: linear-gradient(135deg, #343a40, #17a2b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Modifier le rendez-vous</span>
                    </span>
                </h2>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="modifier_rendezvous.php?id=<?= $id_rdv ?>">
                    <input type="hidden" name="update_rdv" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Médecin</label>
                        <input type="text" class="form-control" 
                               value="Dr. <?= htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']) ?> (<?= htmlspecialchars($rdv['specialite']) ?>)" 
                               readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_rdv" class="form-label">Date du rendez-vous</label>
                            <input type="date" class="form-control" id="date_rdv" name="date_rdv" 
                                   value="<?= htmlspecialchars($rdv['date_rdv']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="heure_rdv" class="form-label">Heure du rendez-vous</label>
                            <input type="time" class="form-control" id="heure_rdv" name="heure_rdv" 
                                   value="<?= substr($rdv['heure_rdv'], 0, 5) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif de consultation</label>
                        <textarea class="form-control" id="motif" name="motif" rows="3"><?= htmlspecialchars($rdv['motif']) ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="mes_rendez_vous.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary" id="saveButton">
                            <span id="buttonText">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </span>
                            <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="pt-5 pb-4 bg-dark text-white">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Contactez-nous</h5>
                    <p><i class="fas fa-envelope me-3"></i> contact@dentalcare.com</p>
                    <p><i class="fas fa-phone me-3"></i> +216 20 123 456</p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Adresse</h5>
                    <p><i class="fas fa-map-marker-alt me-3"></i> 123 Rue de la Santé, Tunis, Tunisie</p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Horaires</h5>
                    <p><i class="fas fa-clock me-3"></i> Lundi - Vendredi: 8h - 18h</p>
                    <p><i class="fas fa-clock me-3"></i> Samedi: 9h - 13h</p>
                    <p><i class="fas fa-times-circle me-3"></i> Dimanche: Fermé</p>
                </div>
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4">Suivez-nous 🦷</h5>
                    <a href="#" class="me-4 text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <hr class="my-4">
            <div class="row text-center">
                <div class="col-md-12">
                    <p class="mb-0">&copy; 2023 DentalCare. Votre sourire, notre priorité <br>Réalisé par Emna BenHarb (G1) et Ameni Nagati (G2)</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation : la date doit être dans le futur
        document.getElementById('date_rdv').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('⚠️ La date doit être dans le futur !');
                this.value = '';
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
            
            // Gestion du bouton d'enregistrement
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('buttonText').classList.add('d-none');
                document.getElementById('loadingSpinner').classList.remove('d-none');
                document.getElementById('saveButton').disabled = true;
            });
        });
    </script>
</body>
</html>