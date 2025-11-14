<?php
require_once 'config.php';
require_once 'auth_check.php';

check_authorization(['SECRETAIRE']);

$message_succes = '';
$message_erreur = '';

try {
    // donneurs
    $stmt_donneurs = $pdo->query("SELECT id_donneur, cin, CONCAT(nom, ' ', prenom) AS nom_complet FROM donneurs ORDER BY nom_complet");
    $donneurs = $stmt_donneurs->fetchAll();
    
    //  centres
    $stmt_centres = $pdo->query("SELECT id_centre, nom_centre FROM centres_collecte ORDER BY nom_centre");
    $centres = $stmt_centres->fetchAll();

} catch (PDOException $e) {
    $message_erreur = "Erreur lors du chargement des listes : Vérifiez la connexion DB et les tables.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_donneur = $_POST['id_donneur'] ?? null;
    $id_centre = $_POST['id_centre'] ?? null;
    $quantite_ml = $_POST['quantite_ml'] ?? null;
    
    // Ajout de la validation du nouveau champ quantite_ml
    if (empty($id_donneur) || empty($id_centre) || !is_numeric($quantite_ml) || $quantite_ml <= 0) {
        $message_erreur = "Veuillez sélectionner le donneur, le centre et entrer une quantité valide.";
    } else {
        try {
            $statut_initial = 'EN STOCK';
            $date_don = date('Y-m-d'); // Date du jour 

            // Mise à jour de la requête pour inclure date_don et quantite_ml
            $sql = "INSERT INTO dons (id_donneur, id_centre, date_don, quantite_ml, statut) 
                    VALUES (:id_donneur, :id_centre, :date_don, :quantite_ml, :statut)";
                    
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':id_donneur', $id_donneur, PDO::PARAM_INT);
            $stmt->bindParam(':id_centre', $id_centre, PDO::PARAM_INT);
            $stmt->bindParam(':date_don', $date_don, PDO::PARAM_STR); // Nouveau champ
            $stmt->bindParam(':quantite_ml', $quantite_ml, PDO::PARAM_INT); // Nouveau champ
            $stmt->bindParam(':statut', $statut_initial, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $message_succes = "Don enregistré avec succès (Statut: EN STOCK). ID du don: " . $pdo->lastInsertId();
            
        } catch (PDOException $e) {
            $message_erreur = "Erreur PDO lors de l'enregistrement du don.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrement de Don</title>
</head>
<body>
    <h1>Enregistrement d'un nouveau Don</h1>
    
    <?php if ($message_succes): ?>
        <div class="message-succes"><?= htmlspecialchars($message_succes) ?></div>
    <?php endif; ?>
    
    <?php if ($message_erreur): ?>
        <div class="message-erreur"><?= htmlspecialchars($message_erreur) ?></div>
    <?php endif; ?>

    <form action="dons_enregistrer.php" method="POST">
        
        <div>
            <label for="id_donneur">Donneur</label>
            <select id="id_donneur" name="id_donneur" required>
                <option value="" disabled selected>Sélectionner le donneur...</option>
                <?php foreach ($donneurs as $donneur): ?>
                    <option value="<?= htmlspecialchars($donneur['id_donneur']) ?>">
                        <?= htmlspecialchars($donneur['nom_complet']) ?> (CIN: <?= htmlspecialchars($donneur['cin']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="id_centre">Centre de Collecte</label>
            <select id="id_centre" name="id_centre" required>
                <option value="" disabled selected>Sélectionner le centre...</option>
                <?php foreach ($centres as $centre): ?>
                    <option value="<?= htmlspecialchars($centre['id_centre']) ?>">
                        <?= htmlspecialchars($centre['nom_centre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="quantite_ml">Quantité (ml)</label>
            <input type="number" id="quantite_ml" name="quantite_ml" required min="1" value="450">
        </div>
        
        <button type="submit" >Enregistrer le Don</button>
    </form>
</body>
</html>