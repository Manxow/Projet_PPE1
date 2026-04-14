<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Vérifier le match 13
echo "=== Match 13 (Titans vs Faucons) ===\n";
$match = $pdo->query("SELECT * FROM rencontre WHERE id_rencontre = 13")->fetch();
print_r($match);

echo "\n=== Timing ===\n";
$date_match = strtotime($match['date_match']);
$heure_saisie = $date_match + (2 * 3600);
$maintenant = time();

echo "Date du match : " . date('Y-m-d H:i:s', $date_match) . "\n";
echo "Heure de saisie : " . date('Y-m-d H:i:s', $heure_saisie) . "\n";
echo "Maintenant : " . date('Y-m-d H:i:s', $maintenant) . "\n";
echo "Peut saisir ? " . ($maintenant >= $heure_saisie ? "OUI" : "NON") . "\n";

// Vérifier les joueurs capitaines
echo "\n=== Capitaines ===\n";
$captains = $pdo->query("SELECT e.id_equipe, e.nom, e.id_createur, j.user FROM equipe e LEFT JOIN joueur j ON e.id_createur = j.id_joueur WHERE e.id_equipe IN (23, 27)")->fetchAll();
print_r($captains);
