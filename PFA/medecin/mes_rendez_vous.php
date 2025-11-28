<?php
session_start();
require_once 'connecte_bd.php';

// Vérifier si le patient est connecté
if (!isset($_SESSION['patient'])) {
    header('Location: index.html');
    exit;
}

// Récupérer les infos du patient
$id_patient = $_SESSION['patient']['id'];
$nom = $_SESSION['patient']['nom'];
$prenom = $_SESSION['patient']['prenom'];

// Connexion à la base de données avec MySQLi
$mysqli = new mysqli("localhost", "root", "", "medical");

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données: " . $mysqli->connect_error);
}

// Traitement de la suppression si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rdv'])) {
    $id_rdv = $_POST['id_rdv'];
    
    $delete_query = "DELETE FROM rendez_vous WHERE id = ? AND id_patient = ?";
    $stmt = $mysqli->prepare($delete_query);
    $stmt->bind_param("ii", $id_rdv, $id_patient);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Le rendez-vous a été supprimé avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression du rendez-vous.";
    }
    
    $stmt->close();
    header("Location: mes_rendez_vous.php");
    exit;
}

// Récupérer les rendez-vous du patient avec les infos des médecins
$query = "SELECT r.*, m.nom AS medecin_nom, m.prenom AS medecin_prenom, m.specialite 
          FROM rendez_vous r
          JOIN medecin m ON r.id_medecin = m.id
          WHERE r.id_patient = ?
          ORDER BY r.date_rdv DESC, r.heure_rdv DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_patient);
$stmt->execute();
$result = $stmt->get_result();
$rendez_vous = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous - DentalCare</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root {
        --primary: #0d6efd;
        --success: #198754;
        --danger: #dc3545;
        --warning: #ffc107;
        --light: #fff;
        --dark: #212529;
        --body-bg: #f8f9fa;
        --card-bg: #fff;
        --text-color: #212529;
        --header-bg: #343a40;
        --footer-bg: #212529;
        --footer-text: #ffffff;
    }

    /* Dark mode variables */
    [data-theme="dark"] {
        --body-bg: #121212;
        --card-bg: #1e1e1e;
        --text-color: #f8f9fa;
        --header-bg: #1a1a1a;
        --footer-bg: #1a1a1a;
        --footer-text: #ffffff;
    }

    /* Design général */
    body {
        font-family: var(--bs-font-sans-serif);
        background-color: var(--body-bg);
        color: var(--text-color);
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Logo */
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
    }
    .navbar-brand span {
        color: var(--primary);
    }

    /* Carte RDV */
    .rdv-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid var(--primary);
        background-color: var(--card-bg);
    }
    .rdv-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .75rem 1.25rem rgba(0,0,0,0.075);
    }

    /* Statuts */
    .rdv-accepted {
        border-left-color: var(--success) !important;
    }
    .rdv-rejected {
        border-left-color: var(--danger) !important;
    }
    .rdv-pending {
        border-left-color: var(--warning) !important;
    }

    /* Badges */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-weight: 600;
        display: inline-block;
    }
    .badge-accepted {
        background-color: var(--success);
        color: white;
    }
    .badge-rejected {
        background-color: var(--danger);
        color: white;
    }
    .badge-pending {
        background-color: var(--warning);
        color: var(--dark);
    }

    /* Image médecin */
    .medecin-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #dee2e6;
    }

    /* Bouton rond (action) */
    .action-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    /* Modal de confirmation */
    .confirm-modal .modal-body {
        padding: 2rem;
        text-align: center;
    }
    .confirm-modal .modal-body i {
        font-size: 3rem;
        color: var(--danger);
        margin-bottom: 1rem;
    }

    /* Header */
    .navbar {
        background-color: var(--header-bg) !important;
    }

    /* Footer */
    footer {
        background-color: var(--footer-bg) !important;
        color: var(--footer-text);
    }

    /* Cards */
    .card {
        background-color: var(--card-bg);
    }

    /* Text colors */
    .text-muted {
        color: #6c757d !important;
    }

    /* Theme toggle buttons */
    .theme-toggle {
        border: none;
        background: transparent;
        padding: 0.5rem;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .theme-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .theme-toggle i {
        font-size: 1.2rem;
    }
    .theme-toggle.active {
        background-color: rgba(255, 255, 255, 0.2);
    }

    /* Texte des rendez-vous en mode sombre */
    [data-theme="dark"] .rdv-card,
    [data-theme="dark"] .rdv-card h5,
    [data-theme="dark"] .rdv-card .text-muted {
        color: white !important;
    }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
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
                
                <ul class="navbar-nav ms-auto">
                    <!-- Theme toggle buttons -->
                    <li class="nav-item d-flex align-items-center me-2">
                        <button class="theme-toggle" id="lightThemeBtn" title="Mode clair">
                            <i class="fas fa-sun"></i>
                        </button>
                        <button class="theme-toggle active" id="darkThemeBtn" title="Mode sombre">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    
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
    </nav>

    <!-- Contenu principal -->
    <main class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="mb-4">Mes Rendez-vous</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="home_patient.php">Accueil</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mes Rendez-vous</li>
                        </ol>
                    </nav>
                </div>
            </div>
            
            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12">
                    <?php if (empty($rendez_vous)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-times me-2"></i> Vous n'avez aucun rendez-vous pour le moment.
                            <a href="rendez_vous.php" class="alert-link">Prendre un rendez-vous</a>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrer</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Statut</label>
                                        <select class="form-select" id="filterStatus">
                                            <option value="all">Tous les statuts</option>
                                            <option value="accepter">Acceptés</option>
                                            <option value="en attente">En attente</option>
                                            <option value="refuser">Refusés</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date de</label>
                                        <input type="date" class="form-control" id="filterDateFrom">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date à</label>
                                        <input type="date" class="form-control" id="filterDateTo">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-4" id="rdvContainer">
                            <?php foreach ($rendez_vous as $rdv): 
                                // Déterminer la classe CSS en fonction du statut
                                $status_class = '';
                                $badge_class = '';
                                if ($rdv['statut'] === 'accepter') {
                                    $status_class = 'rdv-accepted';
                                    $badge_class = 'badge-accepted';
                                } elseif ($rdv['statut'] === 'refuser') {
                                    $status_class = 'rdv-rejected';
                                    $badge_class = 'badge-rejected';
                                } else {
                                    $status_class = 'rdv-pending';
                                    $badge_class = 'badge-pending';
                                }
                            ?>
                            <div class="col-12 rdv-item" 
                                 data-status="<?php echo $rdv['statut']; ?>"
                                 data-date="<?php echo $rdv['date_rdv']; ?>">
                                <div class="card rdv-card <?php echo $status_class; ?>">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 col-lg-1 text-center mb-3 mb-md-0">
                                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($rdv['medecin_prenom'] . '+' . $rdv['medecin_nom']); ?>&background=random" 
                                                     class="medecin-img" alt="Dr. <?php echo $rdv['medecin_prenom']; ?>">
                                            </div>
                                            <div class="col-md-4 col-lg-5">
                                                <h5 class="mb-1">Dr. <?php echo htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?></h5>
                                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($rdv['specialite']); ?></p>
                                                <span class="status-badge <?php echo $badge_class; ?>">
                                                    <?php 
                                                    if ($rdv['statut'] === 'accepter') {
                                                        echo '<i class="fas fa-check-circle me-1"></i> Accepté';
                                                    } elseif ($rdv['statut'] === 'refuser') {
                                                        echo '<i class="fas fa-times-circle me-1"></i> Refusé';
                                                    } else {
                                                        echo '<i class="fas fa-clock me-1"></i> En attente';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-calendar-day text-primary me-2"></i>
                                                    <div>
                                                        <div><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></div>
                                                        <div class="text-muted small"><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3 text-md-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <?php if ($rdv['statut'] === 'en attente' || ($rdv['statut'] === 'accepter' && strtotime($rdv['date_rdv']) > time())): ?>
                                                        <a href="modifier_rendezvous.php?id=<?php echo $rdv['id']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit me-1"></i> Modifier
                                                        </a>
                                                        
                                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $rdv['id']; ?>">
                                                            <i class="fas fa-trash-alt me-1"></i> Supprimer
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($rdv['motif'])): ?>
                                                        <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" 
                                                                title="<?php echo htmlspecialchars($rdv['motif']); ?>">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade confirm-modal" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h5 class="mt-3">Confirmer la suppression</h5>
                    <p>Êtes-vous sûr de vouloir supprimer ce rendez-vous ? Cette action est irréversible.</p>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="delete_rdv" value="1">
                            <input type="hidden" name="id_rdv" id="rdvIdToDelete">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Filtrer les rendez-vous
        document.getElementById('filterStatus').addEventListener('change', filterRDVs);
        document.getElementById('filterDateFrom').addEventListener('change', filterRDVs);
        document.getElementById('filterDateTo').addEventListener('change', filterRDVs);
        
        function filterRDVs() {
            const statusFilter = document.getElementById('filterStatus').value;
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;
            
            document.querySelectorAll('.rdv-item').forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                const itemDate = item.getAttribute('data-date');
                
                let statusMatch = statusFilter === 'all' || itemStatus === statusFilter;
                let dateMatch = true;
                
                if (dateFrom && itemDate < dateFrom) {
                    dateMatch = false;
                }
                
                if (dateTo && itemDate > dateTo) {
                    dateMatch = false;
                }
                
                if (statusMatch && dateMatch) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Gestion de la suppression
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const rdvId = this.getAttribute('data-id');
                document.getElementById('rdvIdToDelete').value = rdvId;
                
                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                modal.show();
            });
        });

        // Gestion du thème sombre/clair
        const lightThemeBtn = document.getElementById('lightThemeBtn');
        const darkThemeBtn = document.getElementById('darkThemeBtn');
        const body = document.body;

        // Vérifier le thème stocké dans localStorage
        const currentTheme = localStorage.getItem('theme') || 'dark';
        setTheme(currentTheme);

        // Écouteurs d'événements pour les boutons de thème
        lightThemeBtn.addEventListener('click', () => setTheme('light'));
        darkThemeBtn.addEventListener('click', () => setTheme('dark'));

        function setTheme(theme) {
            if (theme === 'light') {
                body.setAttribute('data-theme', 'light');
                lightThemeBtn.classList.add('active');
                darkThemeBtn.classList.remove('active');
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                darkThemeBtn.classList.add('active');
                lightThemeBtn.classList.remove('active');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>