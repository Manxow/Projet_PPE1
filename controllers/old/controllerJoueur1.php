<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../models/ModelJoueur1.php';
require_once '../models/ModelEquipe1.php';

$listeEquipes = ModelEquipe::getAllEquipes();

require_once '../views/inscriptionjoueur1.php';

if (!empty($_POST)) {

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $poste = $_POST['poste'];
    $niveau = $_POST['lvl'];
    $equipe = $_POST['team'];

    $joueur = new ModelJoueur($nom, $prenom, $poste, $niveau, $equipe);

    $id_user = $joueur->SelectIdUser();
    $addId_use = $joueur->setIdUtilisateur($id_user);
    $estAjouté = $joueur->AjouterJoueur();
    $_SESSION['joueur'] = $joueur;

    $_SESSION['idTeam'] = $equipe;
    $equipeDuJoueur = new ModelEquipe($equipe);
    $nomTeam = $equipeDuJoueur->getNomEquipe();
    $_SESSION['nomTeam'] = $nomTeam;


    if ($estAjouté = 1) {
        header('Location: ../views/profile1.php');
    } else {
        $error = "Probleme de création du joueur";
        echo $error;
    }
}
