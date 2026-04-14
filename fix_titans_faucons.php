<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Chercher le match Titans vs Faucons existant
echo "=== Matchs Titans vs Faucons ===\n";
$result = $pdo->query("SELECT * FROM rencontre WHERE (id_equipe1 = 23 AND id_equipe2 = 27) OR (id_equipe1 = 27 AND id_equipe2 = 23)")->fetchAll();
print_r($result);

// Supprimer le match 26
echo "\n=== Suppression du match 26 ===\n";
$pdo->exec("DELETE FROM rencontre WHERE id_rencontre = 26");
echo "✅ Match 26 supprimé\n";

// Assigner une heure au match existant Titans vs Faucons
echo "\n=== Modification du match existant ===\n";
if (!empty($result)) {
    $id_match = $result[0]['id_rencontre'];
    $heure_match = date('Y-m-d H:i:s', strtotime('-3 hours'));

    $sql = "UPDATE rencontre SET date_match = ? WHERE id_rencontre = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$heure_match, $id_match]);

    echo "✅ Match $id_match : Heure assignée à $heure_match\n";
    echo "Saisie possible depuis : " . date('Y-m-d H:i:s', strtotime($heure_match . ' +2 hours')) . "\n";
}
