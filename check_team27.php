<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Vérifier l'équipe 27
echo "=== Équipe 27 ===\n";
$equipe27 = $pdo->query('SELECT * FROM equipe WHERE id_equipe = 27')->fetch();
print_r($equipe27);

echo "\n=== Inscription au tournoi 8 ===\n";
$inscription = $pdo->query('SELECT * FROM inscription_tournoi WHERE id_equipe = 27 AND id_tournoi = 8')->fetch();
var_dump($inscription);

echo "\n=== Match 25 ===\n";
$match25 = $pdo->query('SELECT * FROM rencontre WHERE id_rencontre = 25')->fetch();
print_r($match25);

echo "\n=== Tous les matchs de l'équipe 27 ===\n";
$matchs27 = $pdo->query('SELECT * FROM rencontre WHERE id_equipe1 = 27 OR id_equipe2 = 27')->fetchAll();
print_r($matchs27);
