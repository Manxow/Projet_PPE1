<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Vérifier les poules des équipes
echo "=== Poules des équipes ===\n";
$result = $pdo->query("SELECT e.id_equipe, e.nom, i.poule FROM equipe e 
                       LEFT JOIN inscription_tournoi i ON e.id_equipe = i.id_equipe AND i.id_tournoi = 8
                       WHERE e.id_equipe IN (23, 27, 32)")->fetchAll();
print_r($result);

// Supprimer le match 25
echo "\n=== Suppression du match 25 ===\n";
$pdo->exec("DELETE FROM rencontre WHERE id_rencontre = 25");
echo "✅ Match 25 supprimé\n";

// Créer les Titans vs Les Faucons
echo "\n=== Création du nouveau match ===\n";
$sql_insert = "INSERT INTO rencontre (id_tournoi, id_poule, id_equipe1, id_equipe2, phase, statut, date_match) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql_insert);

$heure_match = date('Y-m-d H:i:s', strtotime('-3 hours')); // Il y a 3 heures
$result = $stmt->execute([8, 'C', 23, 27, 'poule', 'à_jouer', $heure_match]);

if ($result) {
    $id_match = $pdo->lastInsertId();
    echo "✅ Nouveau match créé : ID $id_match\n";
    echo "Les Titans (ID 23) vs Les Faucons (ID 27)\n";
    echo "Poule : C\n";
    echo "Heure du match : $heure_match\n";
    echo "Saisie possible depuis : " . date('Y-m-d H:i:s', strtotime($heure_match . ' +2 hours')) . "\n";
}
