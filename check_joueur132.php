<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Vérifier le joueur 132 (Joueur_27_1)
echo "=== Joueur_27_1 ===\n";
$joueur = $pdo->query("SELECT * FROM joueur WHERE id_joueur = 132")->fetch();
print_r($joueur);

echo "\n=== Capitaine de quelle équipe? ===\n";
$equipe = $pdo->query("SELECT * FROM equipe WHERE id_createur = 132")->fetch();
print_r($equipe);
