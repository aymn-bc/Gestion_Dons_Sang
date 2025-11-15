<?php
require_once 'config.php';
require_once 'auth_check.php';

check_authorization(['Medecin']);

$message_succes = '';
$message_erreur = '';

try {
    // 2. A. LISTE DES DONS À TESTER (Statut = 'EN STOCK')
    $stmt = $pdo->query("
        SELECT 
            d.id_don, d.date, 
            dn.nom AS donneur_nom, 
            dn.groupe_sanguin
        FROM dons d
        JOIN donneurs dn ON d.id_donneur = dn.id_donneur
        WHERE d.statut = 'EN STOCK'
        ORDER BY d.date ASC
    ");
    $dons_en_stock = $stmt->fetchAll();

    // 2. B. LOGIQUE DE VALIDATION (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider_test') {
        $id_don = $_POST['id_don'] ?? null;
        $est_conforme = (int)($_POST['est_conforme'] ?? 0); // 1: Conforme (VALIDE), 0: Non-conforme (REJETÉ)
        $resultats = $_POST['resultats'] ?? '';
        
        if (empty($id_don)) {
            $message_erreur = "ID du don manquant pour la validation.";
        } else {
            // Définir le nouveau statut
            $nouveau_statut = $est_conforme ? 'VALIDE' : 'REJETÉ';

            // Démarrer la transaction pour garantir que soit les 2 requêtes réussissent, soit les 2 échouent.
            $pdo->beginTransaction(); 

            try {
                // ÉTAPE 1: Insertion dans la table tests_don
                $stmt_test = $pdo->prepare("INSERT INTO tests_don (id_don, est_conforme, resultats_details) 
                                            VALUES (:id_don, :conforme, :details)");
                $stmt_test->bindParam(':id_don', $id_don, PDO::PARAM_INT);
                $stmt_test->bindParam(':conforme', $est_conforme, PDO::PARAM_INT);
                $stmt_test->bindParam(':details', $resultats, PDO::PARAM_STR);
                $stmt_test->execute();

                // ÉTAPE 2: Mise à jour dans la table dons
                $stmt_update = $pdo->prepare("UPDATE dons SET statut = :statut WHERE id_don = :id_don");
                $stmt_update->bindParam(':statut', $nouveau_statut, PDO::PARAM_STR);
                $stmt_update->bindParam(':id_don', $id_don, PDO::PARAM_INT);
                $stmt_update->execute();

                $pdo->commit();
                $message_succes = "Validation enregistrée. Le don ID #{$id_don} est désormais : **{$nouveau_statut}**.";

                // Recharger la liste des dons en stock
                $dons_en_stock = $pdo->query("
                    SELECT d.id_don, d.date ,dn.nom AS donneur_nom, dn.groupe_sanguin
                    FROM dons d JOIN donneurs dn ON d.id_donneur = dn.id_donneur
                    WHERE d.statut = 'EN STOCK'
                    ORDER BY d.date  ASC
                ")->fetchAll();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $message_erreur = "Erreur lors de la validation du test : " . $e->getMessage();
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
    <title>Validation des Tests</title>
</head>
<body>
    <h1>Validation des Tests des Dons (Statut: EN STOCK)</h1>
    
    <?php if ($message_succes): ?>
        <div class="message-succes"><?= htmlspecialchars($message_succes) ?></div>
    <?php endif; ?>
    <?php if ($message_erreur): ?>
        <div class="message-erreur"><?= htmlspecialchars($message_erreur) ?></div>
    <?php endif; ?>

    <?php if (empty($dons_en_stock)): ?>
        <p>Aucun don n'est actuellement en attente de validation de test.</p>
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
                <?php foreach ($dons_en_stock as $don): ?>
                <tr>
                    <td><?= htmlspecialchars($don['id_don']) ?></td>
                    <td><?= htmlspecialchars($don['donneur_nom']) ?></td>
                    <td><?= htmlspecialchars($don['groupe_sanguin']) ?></td>
                    <td><?= htmlspecialchars($don['date']) ?></td>
                    <td>
                        <button type="button" class="btn btn-info" onclick="openModal(<?= $don['id_don'] ?>)">
                            Valider Test
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div id="validationModal" class="modal">
      <div class="modal-content">
          <span class="close-btn" onclick="closeModal()">&times;</span>
          <h3>Enregistrer les Résultats de Test pour le Don #<span id="don-id-display"></span></h3>
          <form method="POST" action="dons_tests_validation.php">
            <input type="hidden" name="action" value="valider_test">
            <input type="hidden" name="id_don" id="modal-don-id">
            
            <label >Résultat du Test :</label>
            <div>
                <input type="radio" name="est_conforme" id="conforme" value="1" required>
                <label for="conforme" >Conforme (VALIDE)</label>
                
                <input type="radio" name="est_conforme" id="non_conforme" value="0" required style="margin-left: 20px;">
                <label for="non_conforme" >Non Conforme (REJETÉ)</label>
            </div>
            
            <label for="resultats" >Détails des Résultats (Optionnel)</label>
            <textarea name="resultats" id="resultats" ></textarea>
            
            <button type="submit" class="btn btn-primary" >Enregistrer la Validation</button>
          </form>
      </div>
    </div>
    
    <script>
    var modal = document.getElementById('validationModal');
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
