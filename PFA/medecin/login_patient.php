<?php
require_once 'connecte_bd.php';
session_start();

header('Content-Type: application/json'); // Dis que la réponse est JSON

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Sécuriser les entrées
$email = mysqli_real_escape_string($conn, $email);
$password = mysqli_real_escape_string($conn, $password);

// Chercher le patient
$sql = "SELECT * FROM patient WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    
    $_SESSION['patient'] = [
        'id' => $patient['id'],
        'nom' => $patient['nom'],
        'prenom' => $patient['prenom'],
        'email' => $patient['email']
    ];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
}
?>
