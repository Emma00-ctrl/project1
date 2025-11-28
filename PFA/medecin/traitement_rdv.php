<?php
session_start();
require_once 'connecte_bd.php';

// Vérifier que le médecin est connecté
if (!isset($_SESSION['medecin_id'])) {
    header('Location: login_medecin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rdv_id = intval($_POST['rdv_id']);
    $action = $_POST['action'];

    if ($action == 'accepter') {
        $new_status = 'accepté';
        $message_patient = "Votre rendez-vous a été accepté.";
    } elseif ($action == 'refuser') {
        $new_status = 'refusé';
        $message_patient = "Votre rendez-vous a été refusé.";
    } else {
        header('Location: mes_rendez_vous.php');
        exit();
    }

    // 1. Mettre à jour le statut du rendez-vous
    $update_sql = "UPDATE rendez_vous SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $new_status, $rdv_id);

    if ($stmt->execute()) {
        // 2. Enregistrer le message pour le patient dans la base de données
        $insert_message_sql = "INSERT INTO messages_patient (rdv_id, message) VALUES (?, ?)";
        $stmt_msg = $conn->prepare($insert_message_sql);
        $stmt_msg->bind_param('is', $rdv_id, $message_patient);
        $stmt_msg->execute();
        $stmt_msg->close();

        // 3. Envoyer un email au patient
        // On récupère l'email du patient lié à ce rendez-vous
        $sql_patient = "SELECT p.email, p.nom FROM patients p
                        INNER JOIN rendez_vous r ON p.id = r.patient_id
                        WHERE r.id = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param('i', $rdv_id);
        $stmt_patient->execute();
        $result = $stmt_patient->get_result();

        if ($row = $result->fetch_assoc()) {
            $to = $row['email'];
            $subject = "Mise à jour de votre rendez-vous";
            $message = "Bonjour " . htmlspecialchars($row['nom']) . ",\n\n" . $message_patient . "\n\nMerci de votre confiance.";
            $headers = "From: clinique@example.com\r\n" .
                       "Reply-To: clinique@example.com\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            mail($to, $subject, $message, $headers);
        }

        $stmt_patient->close();
    }

    $stmt->close();
}

$conn->close();

// Rediriger vers la page de rendez-vous
header('Location: mes_rendezvous.php');
exit();
?>
