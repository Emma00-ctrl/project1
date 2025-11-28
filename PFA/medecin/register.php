<?php
require_once 'connecte_bd.php';

$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];
$password = $_POST['password'];
$telephone = $_POST['telephone'];

// Ajouter dans la table patient
$sql = "INSERT INTO patient (prenom, nom, email, password, date_naissance, image)
        VALUES ('$prenom', '$nom', '$email', '$password', '2000-01-01', '')";

if ($conn->query($sql) === TRUE) {
    echo "Compte créé avec succès.";
} else {
    echo "Erreur: " . $conn->error;
}
?>
