<?php
require_once 'config/database.php';
require_once 'models/ModelJoueur.php';
$p = ModelJoueur::getJoueurByPseudo('Joueur_27_1');
var_export($p);
