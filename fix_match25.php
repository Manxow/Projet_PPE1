<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Corriger le match 25 en assignant la poule
$sql = "UPDATE rencontre SET id_poule = 'C' WHERE id_rencontre = 25";
$result = $pdo->exec($sql);

echo "✅ Match 25 corrigé ! Poule assignée à 'C'\n";

// Vérifier
$match = $pdo->query('SELECT * FROM rencontre WHERE id_rencontre = 25')->fetch();
echo "Vérification du match 25:\n";
print_r($match);
