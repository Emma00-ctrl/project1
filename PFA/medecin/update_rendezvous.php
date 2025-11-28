<?php
require_once 'connecte_bd.php';

$id = $_POST['id'];
$action = $_POST['action'];

// Mise à jour du status : accepter = 1, refuser = 0
$status = ($action == 'accepter') ? 1 : 0;

$sql = "UPDATE rendezvous SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $status, $id);

if ($stmt->execute()) {
    echo "Rendez-vous mis à jour.";
} else {
    echo "Erreur lors de la mise à jour.";
}
?>
