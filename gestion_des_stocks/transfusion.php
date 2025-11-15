<?php
require_once 'config.php';
require_once 'auth_check.php';

check_authorization(['Admin']);

$message_succes = '';
$message_erreur = '';

try {
    // 2. A. LISTE DES DONS PRÊTS À ÊTRE TRANSFUSÉS (Statut = 'VALIDE')
    $stmt = $pdo->query("
        SELECT 
            d.id_don, d.date,
            dn.nom AS donneur_nom, 
            dn.groupe_sanguin
        FROM dons d
        JOIN donneurs dn ON d.id_donneur = dn.id_donneur
        WHERE d.statut = 'VALIDE'
        ORDER BY d.date ASC
    ");
    $dons_valides = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enregistrer_transfusion') {
        $id_don = $_POST['id_don'] ?? null;
        $hopital_recepteur = $_POST['hopital_recepteur'] ?? '';
        
        if (empty($id_don) || empty($hopital_recepteur)) {
            $message_erreur = "ID du don et nom de l'hôpital récepteur sont requis.";
        } else {
            // Définir les valeurs pour l'insertion/mise à jour
            $nouveau_statut = 'UTILISÉ';
            $date_transfusion = date('Y-m-d'); // Utilise la date du jour pour la traçabilité

            $pdo->beginTransaction(); 

            try {
                // Insertion dans la table transfusions
                $stmt_trans = $pdo->prepare("INSERT INTO transfusions (id_don, hopital_recepteur, date_transfusion) 
                                            VALUES (:id_don, :hopital, :date)");
                $stmt_trans->bindParam(':id_don', $id_don, PDO::PARAM_INT);
                $stmt_trans->bindParam(':hopital', $hopital_recepteur, PDO::PARAM_STR);
                $stmt_trans->bindParam(':date', $date_transfusion, PDO::PARAM_STR);
                $stmt_trans->execute();

                //  Mise à jour dans la table dons 
                $stmt_update = $pdo->prepare("UPDATE dons SET statut = :statut WHERE id_don = :id_don");
                $stmt_update->bindParam(':statut', $nouveau_statut, PDO::PARAM_STR);
                $stmt_update->bindParam(':id_don', $id_don, PDO::PARAM_INT);
                $stmt_update->execute();

                $pdo->commit();
                $message_succes = "Traçabilité enregistrée. Le don ID #{$id_don} est désormais : **UTILISÉ**.";

                // Recharger la liste des dons valides
                $dons_valides = $pdo->query("
                    SELECT d.id_don, d.date ,dn.nom AS donneur_nom, dn.groupe_sanguin
                    FROM dons d JOIN donneurs dn ON d.id_donneur = dn.id_donneur
                    WHERE d.statut = 'VALIDE'
                    ORDER BY d.date ASC
                ")->fetchAll();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $message_erreur = "Erreur lors de l'enregistrement de la transfusion : Ce don pourrait déjà être utilisé. Détails : " . $e->getMessage();
            }
        }
    }

} catch (PDOException $e) {
    $message_erreur = "Erreur de base de données : " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Traçabilité / Transfusion</title>
</head>
<body>
    <h1>Traçabilité : Enregistrement des Transfusions (Statut: VALIDE)</h1>
    
    <?php if ($message_succes): ?>
        <div class="message-succes"><?= htmlspecialchars($message_succes) ?></div>
    <?php endif; ?>
    <?php if ($message_erreur): ?>
        <div class="message-erreur"><?= htmlspecialchars($message_erreur) ?></div>
    <?php endif; ?>

    <?php if (empty($dons_valides)): ?>
        <p style="background: #e6f7ff; padding: 10px; border: 1px solid #91d5ff;">Aucun don n'est actuellement prêt pour la transfusion (Statut: VALIDE).</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Don</th>
                    <th>Donneur</th>
                    <th>Groupe Sanguin</th>
                    <th>Date Don</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dons_valides as $don): ?>
                <tr>
                    <td><?= htmlspecialchars($don['id_don']) ?></td>
                    <td><?= htmlspecialchars($don['donneur_nom']) ?></td>
                    <td><?= htmlspecialchars($don['groupe_sanguin']) ?></td>
                    <td><?= htmlspecialchars($don['date']) ?></td>
                    <td>
                        <button type="button" class="btn btn-success" onclick="openModal(<?= $don['id_don'] ?>)">
                            Transfuser / Utiliser
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div id="transfusionModal" class="modal">
      <div class="modal-content">
          <span class="close-btn" onclick="closeModal()">&times;</span>
          <h3>Enregistrer la Transfusion pour le Don #<span id="don-id-display"></span></h3>
          <form method="POST" action="dons_transfusion.php">
            <input type="hidden" name="action" value="enregistrer_transfusion">
            <input type="hidden" name="id_don" id="modal-don-id">
            
            <label for="hopital_recepteur">Hôpital Récepteur :</label>
            <input type="text" name="hopital_recepteur" id="hopital_recepteur" required>
            
            <p >Le statut passera à 'UTILISÉ' et la date de transfusion sera la date d'aujourd'hui.</p>
            
            <button type="submit" class="btn btn-success" style="margin-top: 20px;">Confirmer la Transfusion</button>
          </form>
      </div>
    </div>
    
    <script>
    var modal = document.getElementById('transfusionModal');
    var donIdInput = document.getElementById('modal-don-id');
    var donIdDisplay = document.getElementById('don-id-display');

    function openModal(donId) {
        donIdInput.value = donId;
        donIdDisplay.textContent = donId;
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target == modal) {
        closeModal();
      }
    }
    </script>
</body>
</html>
