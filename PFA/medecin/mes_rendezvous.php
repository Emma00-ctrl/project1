<?php
session_start();
require_once 'connecte_bd.php';

// Vérifier que le médecin est connecté
if (!isset($_SESSION['medecin_id'])) {
    header('Location: login_medecin.php');
    exit();
}

$medecin_id = $_SESSION['medecin_id'];

// Récupérer les rendez-vous des patients pour ce médecin
$sql = "SELECT r.id, p.nom, p.prenom, r.date_rendez_vous, r.status 
        FROM rendez_vous r 
        JOIN patients p ON r.patient_id = p.id 
        WHERE r.medecin_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $medecin_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Rendez-vous</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        button { padding: 5px 10px; margin: 2px; }
    </style>
</head>
<body>

    <h1>Mes Rendez-vous</h1>

    <table>
        <thead>
            <tr>
                <th>Nom du patient</th>
                <th>Prénom du patient</th>
                <th>Date du rendez-vous</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['date_rendez_vous']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'en attente'): ?>
                            <form action="traitement_rdv.php" method="post" style="display:inline;">
                                <input type="hidden" name="rdv_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="action" value="accepter">
                                <button type="submit" style="background-color: green; color: white;">Accepter</button>
                            </form>
                            <form action="traitement_rdv.php" method="post" style="display:inline;">
                                <input type="hidden" name="rdv_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" style="background-color: red; color: white;">Refuser</button>
                            </form>
                        <?php else: ?>
                            Action déjà effectuée
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
