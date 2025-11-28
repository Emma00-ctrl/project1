<?php
// Vérifie que le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $commentaire = $_POST['commentaire'];
    $note = $_POST['note'];

    // Traitement de l'image uploadée
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dossier = 'uploads/';
        // Créer le dossier s'il n'existe pas
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $nom_fichier = basename($_FILES['image']['name']);
        $chemin = $dossier . time() . '_' . $nom_fichier;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $chemin)) {
            echo "Avis reçu avec succès !<br>";
            echo "Nom : $nom<br>";
            echo "Prénom : $prenom<br>";
            echo "Commentaire : $commentaire<br>";
            echo "Note : $note étoiles<br>";
            echo "<img src='$chemin' style='width:100px; height:100px; border-radius:50%;'><br>";
            echo "<a href='index.html'>Retour</a>";
        } else {
            echo "Erreur lors de l'upload de l'image.";
        }
    } else {
        echo "Image invalide ou manquante.";
    }

} else {
    echo "Formulaire non soumis.";
}
?>
