<?php
session_start();

// Vérifier si le médecin est connecté
if (!isset($_SESSION['medecin'])) {
    header('Location: doctor_login.php');
    exit;
}

// Récupérer les infos du médecin
$id_medecin = $_SESSION['medecin']['id'];
$nom = $_SESSION['medecin']['nom'];
$prenom = $_SESSION['medecin']['prenom'];
$email = $_SESSION['medecin']['email'];
$specialite = $_SESSION['medecin']['specialite'];
$telephone = $_SESSION['medecin']['Telephone']; // Notez le 'T' majuscule pour correspondre à la table
$adresse = $_SESSION['medecin']['Adresse']; // Notez le 'A' majuscule pour correspondre à la table
$annees_experience = $_SESSION['medecin']['Années d\'expérience']; // Avec les backticks pour le nom de champ spécial

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "medical");

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données: " . $mysqli->connect_error);
}

// Traitement des actions (accepter/refuser)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['rdv_id'])) {
        $rdv_id = intval($_POST['rdv_id']);
        $action = $_POST['action'];
        
        // Vérifier que le rendez-vous appartient bien au médecin connecté
        $check_stmt = $mysqli->prepare("SELECT id FROM rendez_vous WHERE id = ? AND id_medecin = ?");
        $check_stmt->bind_param("ii", $rdv_id, $id_medecin);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            if ($action === 'accept' || $action === 'reject') {
                $statut = ($action === 'accept') ? 'accepter' : 'refuser';
                
                $update_stmt = $mysqli->prepare("UPDATE rendez_vous SET statut = ? WHERE id = ?");
                $update_stmt->bind_param("si", $statut, $rdv_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['message'] = ($action === 'accept') 
                        ? "Rendez-vous accepté avec succès" 
                        : "Rendez-vous refusé avec succès";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Erreur lors de la mise à jour du rendez-vous";
                    $_SESSION['message_type'] = "danger";
                }
                $update_stmt->close();
            }
        } else {
            $_SESSION['message'] = "Action non autorisée sur ce rendez-vous";
            $_SESSION['message_type'] = "danger";
        }
        $check_stmt->close();
        
        // Redirection pour éviter la resoumission du formulaire
        header("Location: home_doctor.php#rendez-vous");
        exit;
    }
}

// Récupérer les messages de session
$message = $_SESSION['message'] ?? null;
$message_type = $_SESSION['message_type'] ?? null;
unset($_SESSION['message'], $_SESSION['message_type']);

// Récupérer les rendez-vous avec filtrage
$statut_filter = $_GET['statut'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

$query = "SELECT r.*, p.nom AS patient_nom, p.prenom AS patient_prenom
          FROM rendez_vous r
          JOIN patient p ON r.id_patient = p.id
          WHERE r.id_medecin = ?";

$params = [$id_medecin];
$types = "i";

if ($statut_filter !== 'all') {
    $statut_db = '';
    if ($statut_filter === 'pending') $statut_db = 'en attente';
    elseif ($statut_filter === 'accepted') $statut_db = 'accepter';
    elseif ($statut_filter === 'rejected') $statut_db = 'refuser';
    
    $query .= " AND r.statut = ?";
    $params[] = $statut_db;
    $types .= "s";
}

if (!empty($date_filter)) {
    $query .= " AND r.date_rdv = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$query .= " ORDER BY r.date_rdv ASC, r.heure_rdv ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
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
    <title>Espace Médecin - DentalCare</title>
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
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-brand span {
            color: var(--primary);
        }
        
        .welcome-card {
            border-radius: 15px;
            border-left: 5px solid var(--primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .rdv-card {
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--warning);
        }
        
        .rdv-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .rdv-accepted {
            border-left-color: var(--success);
        }
        
        .rdv-rejected {
            border-left-color: var(--danger);
        }
        
        .rdv-pending {
            border-left-color: var(--warning);
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 50px;
            font-weight: 600;
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
        
        .patient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .filter-active {
            background-color: var(--primary) !important;
            color: white !important;
        }
        
        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        /* Styles pour le profil */
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255,255,255,0.3);
            margin-bottom: 15px;
        }
        
        .profile-body {
            background: white;
            padding: 30px;
        }
        
        .profile-section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .profile-info-item {
            margin-bottom: 15px;
        }
        
        .profile-info-label {
            font-weight: 600;
            color: var(--dark);
            display: block;
            margin-bottom: 5px;
        }
        
        .profile-info-value {
            color: #555;
        }
        
        .quick-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 20px;
        }
        
        .quick-stat {
            padding: 15px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            flex: 1;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .quick-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .quick-stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .quick-stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .section-anchor {
            padding-top: 80px;
            margin-top: -80px;
            display: block;
        }

        /* Mode sombre */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        
        body.dark-mode .card,
        body.dark-mode .table,
        body.dark-mode .table th,
        body.dark-mode .table td,
        body.dark-mode .dropdown-menu,
        body.dark-mode .form-control,
        body.dark-mode .btn-outline-primary {
            background-color: #1e1e1e;
            color: #e0e0e0;
            border-color: #333;
        }
        
        body.dark-mode .card-header,
        body.dark-mode .table thead th {
            background-color: #252525;
            color: #fff;
        }
        
        body.dark-mode .navbar-dark {
            background-color: #1a1a1a !important;
        }
        
        body.dark-mode .profile-header {
            background: linear-gradient(135deg, #0D47A1, #1A237E) !important;
        }
        
        body.dark-mode .text-muted {
            color: #aaa !important;
        }
        
        body.dark-mode .btn-outline-primary {
            color: #1976D2;
            border-color: #1976D2;
        }
        
        body.dark-mode .btn-outline-primary:hover {
            background-color: #1976D2;
            color: white;
        }
        
        body.dark-mode .profile-body,
        body.dark-mode .quick-stat,
        body.dark-mode .profile-info-value,
        body.dark-mode .profile-description {
            background-color: #1e1e1e;
            color: #e0e0e0;
        }
        
        body.dark-mode .profile-info-label {
            color: #e0e0e0;
        }
        
        /* Boutons de bascule */
        .theme-toggle {
            display: flex;
            align-items: center;
            margin-left: 15px;
        }
        
        .theme-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .theme-toggle-btn:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .theme-toggle-btn.active {
            background: var(--primary);
            color: white;
        }
        
        .theme-toggle-label {
            margin-left: 10px;
            font-weight: 500;
        }
    </style>
    <script>
        // Fonction pour basculer entre les modes clair/sombre
        function toggleDarkMode() {
            const body = document.body;
            body.classList.toggle('dark-mode');
            
            // Sauvegarder la préférence dans localStorage
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            
            // Changer l'icône
            const icon = document.getElementById('theme-icon');
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
        
        // Vérifier la préférence au chargement
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
                const icon = document.getElementById('theme-icon');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        });
    </script>
</head>
<body>
    <!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home_doctor.php">Dental<span>Care.</span></a>

        <!-- Boutons visibles même sur petits écrans -->
        <div class="d-flex d-lg-none">
            <!-- Bouton mode sombre -->
            <button class="btn btn-outline-light me-2" onclick="toggleDarkMode()" title="Basculer en mode sombre">
                <i id="theme-icon" class="fas fa-moon"></i>
            </button>

            <!-- Menu utilisateur réduit -->
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-md"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="home_doctor.php#mon-profil"><i class="fas fa-user-edit me-2"></i>Mon profil</a></li>
                    <li><a class="dropdown-item" href="home_doctor.php#rendez-vous"><i class="fas fa-calendar-check me-2"></i>Mes rendez-vous</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout_doctor.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>

        <!-- Bouton de menu hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDoctor">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenu de la navbar -->
        <div class="collapse navbar-collapse" id="navbarDoctor">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="home_doctor.php">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="home_doctor.php#rendez-vous">Mes Rendez-vous</a>
                </li>
            </ul>

            <!-- Boutons visibles uniquement sur écran large -->
            <div class="d-none d-lg-flex align-items-center">
                <div class="theme-toggle me-3">
                    <button class="btn btn-outline-light" onclick="toggleDarkMode()" title="Basculer en mode sombre">
                        <i id="theme-icon" class="fas fa-moon"></i>
                    </button>
                    <span class="theme-toggle-label d-none d-md-inline ms-2">Mode sombre</span>
                </div>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-md me-1"></i> Dr. <?php echo htmlspecialchars($prenom); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="home_doctor.php#mon-profil"><i class="fas fa-user-edit me-2"></i>Mon profil</a></li>
                            <li><a class="dropdown-item" href="home_doctor.php#rendez-vous"><i class="fas fa-calendar-check me-2"></i>Mes rendez-vous</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout_doctor.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
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
            <!-- Message de notification -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Section Bienvenue -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card welcome-card bg-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h2 class="mb-1">Bonjour Dr. <?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h2>
                                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($specialite); ?></p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-primary fs-6"><?php echo date('d/m/Y'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section Profil Professionnel -->
            <div class="profile-card" id="mon-profil">
                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($prenom . '+' . $nom); ?>&background=random&color=fff&size=256" 
                         class="profile-avatar" alt="Dr. <?php echo htmlspecialchars($prenom . ' ' . $nom); ?>">
                    <h3>Dr. <?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h3>
                    <p class="mb-0"><?php echo htmlspecialchars($specialite); ?></p>
                </div>
                
                <div class="profile-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="profile-section-title"><i class="fas fa-info-circle me-2"></i>Informations Professionnelles</h4>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Spécialité</span>
                                <span class="profile-info-value"><?php echo htmlspecialchars($specialite); ?></span>
                            </div>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Années d'expérience</span>
                                <span class="profile-info-value"><?php echo htmlspecialchars($annees_experience); ?></span>
                            </div>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Email</span>
                                <span class="profile-info-value"><?php echo htmlspecialchars($email); ?></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h4 class="profile-section-title"><i class="fas fa-address-card me-2"></i>Coordonnées</h4>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Téléphone</span>
                                <span class="profile-info-value"><?php echo htmlspecialchars($telephone); ?></span>
                            </div>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Adresse</span>
                                <span class="profile-info-value"><?php echo htmlspecialchars($adresse); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="quick-stats mt-4">
                        <div class="quick-stat">
                            <div class="quick-stat-value"><?php echo count(array_filter($rendez_vous, function($rdv) { return $rdv['statut'] === 'accepter'; })); ?></div>
                            <div class="quick-stat-label">RDV Acceptés</div>
                        </div>
                        
                        <div class="quick-stat">
                            <div class="quick-stat-value"><?php echo count(array_filter($rendez_vous, function($rdv) { return $rdv['statut'] === 'en attente'; })); ?></div>
                            <div class="quick-stat-label">RDV En Attente</div>
                        </div>
                        
                        <div class="quick-stat">
                            <div class="quick-stat-value"><?php echo count($rendez_vous); ?></div>
                            <div class="quick-stat-label">Total RDV</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section Rendez-vous -->
            <div class="section-anchor" id="rendez-vous"></div>
            
            <!-- Filtres -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Statut</label>
                                    <div class="btn-group w-100" role="group">
                                        <a href="?statut=all#rendez-vous" class="btn btn-outline-primary <?php echo ($statut_filter === 'all') ? 'filter-active' : ''; ?>">
                                            Tous
                                        </a>
                                        <a href="?statut=pending#rendez-vous" class="btn btn-outline-primary <?php echo ($statut_filter === 'pending') ? 'filter-active' : ''; ?>">
                                            En attente
                                        </a>
                                        <a href="?statut=accepted#rendez-vous" class="btn btn-outline-primary <?php echo ($statut_filter === 'accepted') ? 'filter-active' : ''; ?>">
                                            Acceptés
                                        </a>
                                        <a href="?statut=rejected#rendez-vous" class="btn btn-outline-primary <?php echo ($statut_filter === 'rejected') ? 'filter-active' : ''; ?>">
                                            Refusés
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date</label>
                                    <form method="GET" class="d-flex">
                                        <input type="hidden" name="statut" value="<?php echo $statut_filter; ?>">
                                        <input type="date" class="form-control me-2" name="date" value="<?php echo $date_filter; ?>">
                                        <button type="submit" class="btn btn-primary">Filtrer</button>
                                        <?php if (!empty($date_filter)): ?>
                                            <a href="?statut=<?php echo $statut_filter; ?>#rendez-vous" class="btn btn-outline-danger ms-2">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des rendez-vous -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?php 
                                if ($statut_filter === 'pending') echo 'Rendez-vous en attente';
                                elseif ($statut_filter === 'accepted') echo 'Rendez-vous acceptés';
                                elseif ($statut_filter === 'rejected') echo 'Rendez-vous refusés';
                                else echo 'Tous les rendez-vous';
                                ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($rendez_vous)): ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucun rendez-vous trouvé avec ces critères de filtrage.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date</th>
                                                <th>Heure</th>
                                                <th>Motif</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rendez_vous as $rdv): 
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
                                            <tr class="<?php echo $status_class; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($rdv['patient_prenom'] . '+' . $rdv['patient_nom']); ?>&background=random" 
                                                             class="patient-avatar me-3" alt="<?php echo htmlspecialchars($rdv['patient_prenom']); ?>">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($rdv['heure_rdv'])); ?></td>
                                                <td><?php echo !empty($rdv['motif']) ? htmlspecialchars($rdv['motif']) : '--'; ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $badge_class; ?>">
                                                        <?php 
                                                        if ($rdv['statut'] === 'accepter') echo '<i class="fas fa-check-circle me-1"></i> Accepté';
                                                        elseif ($rdv['statut'] === 'refuser') echo '<i class="fas fa-times-circle me-1"></i> Refusé';
                                                        else echo '<i class="fas fa-clock me-1"></i> En attente';
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($rdv['statut'] === 'en attente'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>">
                                                            <input type="hidden" name="action" value="accept">
                                                            <button type="submit" class="btn btn-success btn-action me-1" title="Accepter">
                                                                <i class="fas fa-check me-1"></i> Accepter
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-danger btn-action" title="Refuser">
                                                                <i class="fas fa-times me-1"></i> Refuser
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">--</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="pt-5 pb-4 bg-dark text-white">
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
                    <a href="#" class="me-4 text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="me-4 text-white"><i class="fab fa-twitter"></i></a>
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
</body>
</html>