<?php
require_once 'connecte_bd.php';
session_start();

header('Content-Type: application/json');

// Récupération des données du formulaire
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validation des entrées
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email et mot de passe sont requis.']);
    exit;
}

// Préparation de la requête avec des paramètres pour éviter les injections SQL
$sql = "SELECT * FROM medecin WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête.']);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $medecin = $result->fetch_assoc();

    // Vérification du mot de passe haché
    if (password_verify($password, $medecin['password'])) {
        // Stockage des informations du médecin en session
        $_SESSION['medecin'] = [
            'id' => $medecin['id'],
            'nom' => $medecin['nom'],
            'prenom' => $medecin['prenom'],
            'email' => $medecin['email'],
            'specialite' => $medecin['specialite'],
            'Telephone' => $medecin['Telephone'],
            'Adresse' => $medecin['Adresse'],
            'Années d\'expérience' => $medecin['Années d\'expérience']
        ];

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
}

// Fermeture des ressources
$stmt->close();
$conn->close();
?>