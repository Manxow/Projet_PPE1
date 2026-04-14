<?php
require_once 'config/database.php';
$pdo = database::Connexion();
$result = $pdo->query('DESCRIBE joueur')->fetchAll();
print_r($result);
