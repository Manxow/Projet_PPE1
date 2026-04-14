<?php
require_once 'config/database.php';
$pdo = database::Connexion();

echo "=== Tournoi 8 ===\n";
$tournoi = $pdo->query('SELECT id_tournoi, nom, statut FROM tournoi WHERE id_tournoi = 8')->fetch();
var_dump($tournoi);
