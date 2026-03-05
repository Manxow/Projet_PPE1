<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../models/ModelUtilisateur1.php';
require_once '../models/ModelEquipe1.php';

$listeEquipes = ModelEquipe::getAllEquipes();


require_once '../views/inscription1.php';


if (!empty($_POST)) {
    $identifiant = $_POST['user'];
    $mdp = $_POST['pw'];

    $_SESSION['nom_utilisateur'] = $_POST['user'];






    $utilisateur = new ModelUtilisateur($identifiant, $mdp);

    $estAjouté = $utilisateur->AjouterUtilisateur();

    if ($estAjouté = 1) {
        header('Location: ../views/inscriptionJoueur1.php');
    } else {
        $error = "Probleme d'inscription";
        echo $error;
    }
}
