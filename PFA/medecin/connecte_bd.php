<?php
// Informations de connexion
$host = 'localhost';     
$username = 'root';       
$password = '';           
$dbname = 'medical'; 

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// echo "Connexion réussie.";
?>
