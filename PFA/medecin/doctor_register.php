<?php
// Initialiser les variables
$errors = [];
$success = false;
$nom = $prenom = $email = $specialite = $telephone = $adresse = $annees_experience = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $specialite = trim($_POST['specialite'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $annees_experience = trim($_POST['annees_experience'] ?? '');

    // Validation
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire";
    }

    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire";
    }

    if (empty($email)) {
        $errors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'email n'est pas valide";
    }

    if (empty($password)) {
        $errors['password'] = "Le mot de passe est obligatoire";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Les mots de passe ne correspondent pas";
    }

    if (empty($specialite)) {
        $errors['specialite'] = "La spécialité est obligatoire";
    }

    if (empty($telephone)) {
        $errors['telephone'] = "Le téléphone est obligatoire";
    } elseif (!preg_match('/^[0-9]{8,15}$/', $telephone)) {
        $errors['telephone'] = "Numéro de téléphone invalide";
    }

    if (empty($adresse)) {
        $errors['adresse'] = "L'adresse est obligatoire";
    }

    if (empty($annees_experience)) {
        $errors['annees_experience'] = "Les années d'expérience sont obligatoires";
    } elseif (!is_numeric($annees_experience) || $annees_experience < 0 || $annees_experience > 60) {
        $errors['annees_experience'] = "Veuillez entrer un nombre valide (0-60)";
    }

    // Si pas d'erreurs, enregistrement en base
    if (empty($errors)) {
        $mysqli = new mysqli("localhost", "root", "", "medical");

        // Vérifier si l'email existe déjà
        $stmt = $mysqli->prepare("SELECT id FROM medecin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors['email'] = "Cet email est déjà utilisé";
        } else {
            // Hachage du mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertion avec tous les champs
            $insert_stmt = $mysqli->prepare("INSERT INTO medecin (nom, prenom, email, password, specialite, Telephone, Adresse, `Années d'expérience`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssisi", $nom, $prenom, $email, $password_hash, $specialite, $telephone, $adresse, $annees_experience);

            if ($insert_stmt->execute()) {
                $success = true;
                // Réinitialiser les champs après succès
                $nom = $prenom = $email = $specialite = $telephone = $adresse = $annees_experience = '';
            } else {
                $errors['database'] = "Erreur lors de l'inscription: " . $mysqli->error;
            }

            $insert_stmt->close();
        }

        $stmt->close();
        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Médecin - DentalCare</title>
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
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary);
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                              url('https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .register-container {
            max-width: 800px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .register-header {
            background-color: var(--primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-body {
            background-color: white;
            padding: 2.5rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 118, 210, 0.25);
        }
        
        .btn-doctor {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-doctor:hover {
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }
        
        .specialite-icon {
            color: var(--primary);
            font-size: 1.2rem;
            margin-right: 10px;
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="register-container">
                    <!-- En-tête -->
                    <div class="register-header">
                        <h1><i class="fas fa-user-md me-2"></i>Inscription Médecin</h1>
                        <p class="mb-0">Rejoignez notre plateforme de gestion dentaire</p>
                    </div>
                    
                    <!-- Corps du formulaire -->
                    <div class="register-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> Inscription réussie! Vous pouvez maintenant vous connecter.
                            </div>
                        <?php elseif (isset($errors['database'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errors['database']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="doctor_register.php">
                            <div class="row g-3">
                                <!-- Nom -->
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input type="text" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                                           id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>">
                                    <?php if (isset($errors['nom'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Prénom -->
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control <?php echo isset($errors['prenom']) ? 'is-invalid' : ''; ?>" 
                                           id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>">
                                    <?php if (isset($errors['prenom'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['prenom']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-12">
                                    <label for="email" class="form-label">Email professionnel</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Mot de passe -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                               id="password" name="password">
                                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                        <?php if (isset($errors['password'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Minimum 8 caractères</small>
                                </div>
                                
                                <!-- Confirmation mot de passe -->
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                               id="confirm_password" name="confirm_password">
                                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                                        <?php if (isset($errors['confirm_password'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Spécialité -->
                                <div class="col-md-6">
                                    <label for="specialite" class="form-label">Spécialité</label>
                                    <select class="form-select <?php echo isset($errors['specialite']) ? 'is-invalid' : ''; ?>" 
                                            id="specialite" name="specialite">
                                        <option value="">Sélectionnez une spécialité</option>
                                        <option value="Dentiste généraliste" <?php echo $specialite === 'Dentiste généraliste' ? 'selected' : ''; ?>>Dentiste généraliste</option>
                                        <option value="Orthodontiste" <?php echo $specialite === 'Orthodontiste' ? 'selected' : ''; ?>>Orthodontiste</option>
                                        <option value="Parodontiste" <?php echo $specialite === 'Parodontiste' ? 'selected' : ''; ?>>Parodontiste</option>
                                        <option value="Pédodontiste" <?php echo $specialite === 'Pédodontiste' ? 'selected' : ''; ?>>Pédodontiste</option>
                                        <option value="Chirurgien dentiste" <?php echo $specialite === 'Chirurgien dentiste' ? 'selected' : ''; ?>>Chirurgien dentiste</option>
                                        <option value="Endodontiste" <?php echo $specialite === 'Endodontiste' ? 'selected' : ''; ?>>Endodontiste</option>
                                    </select>
                                    <?php if (isset($errors['specialite'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['specialite']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Téléphone -->
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['telephone']) ? 'is-invalid' : ''; ?>" 
                                           id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>">
                                    <?php if (isset($errors['telephone'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['telephone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Adresse -->
                                <div class="col-12">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control <?php echo isset($errors['adresse']) ? 'is-invalid' : ''; ?>" 
                                           id="adresse" name="adresse" value="<?php echo htmlspecialchars($adresse); ?>">
                                    <?php if (isset($errors['adresse'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['adresse']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Années d'expérience -->
                                <div class="col-md-6">
                                    <label for="annees_experience" class="form-label">Années d'expérience</label>
                                    <input type="number" min="0" max="60" class="form-control <?php echo isset($errors['annees_experience']) ? 'is-invalid' : ''; ?>" 
                                           id="annees_experience" name="annees_experience" value="<?php echo htmlspecialchars($annees_experience); ?>">
                                    <?php if (isset($errors['annees_experience'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['annees_experience']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Bouton d'inscription -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-doctor w-100">
                                        <i class="fas fa-user-plus me-2"></i>S'inscrire
                                    </button>
                                </div>
                                
                                <!-- Lien vers la connexion -->
                                <div class="col-12 text-center mt-3">
                                    <p class="mb-0">Déjà inscrit? <a href="index.html" class="text-primary">Connectez-vous ici</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher/masquer le mot de passe
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        
        // Validation en temps réel
        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 8) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            if (this.value !== password) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Validation du téléphone
        document.getElementById('telephone').addEventListener('input', function() {
            const phoneRegex = /^[0-9]{8,15}$/;
            if (!phoneRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>