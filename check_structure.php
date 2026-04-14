<?php
require_once 'config/database.php';
$pdo = database::Connexion();
$result = $pdo->query('DESCRIBE equipe')->fetchAll();
print_r($result);
