<?php
session_start();

require_once '../models/ModelJoueur1.php';
require_once '../models/ModelEquipe1.php';
require_once '../models/ModelUtilisateur1.php';


if (!empty($_POST)) {
    $identifiant = $_POST['user'];
    $mdp = $_POST['pw'];

    $_SESSION['nom_utilisateur'] = $_POST['user'];

    $utilisateur = new ModelUtilisateur($identifiant, $mdp);

    $connexion = $utilisateur->getUnUser();

    if ($connexion == 1) {

        $idUser = $utilisateur->getMonId();
        $utilisateur->setId($idUser);
        $player = $utilisateur->getUnJoueurFromUser();

        $monNom = $player['nom'];
        $monPrenom = $player['prenom'];
        $monPoste = $player['poste'];
        $monNiveau = $player['niveau'];
        $monIdEquipe = $player['id_equipe'];

        $monJoueur = new ModelJoueur($monNom, $monPrenom, $monPoste, $monNiveau, $monIdEquipe);
        $_SESSION['joueur'] = $monJoueur;

        $equipeDuJoueur = new ModelEquipe($monIdEquipe);
        $nomTeam = $equipeDuJoueur->getNomEquipe();
        $_SESSION['nomTeam'] = $nomTeam;


        header('Location: ../views/profile1.php');
    } else {
        header('Location: ../views/profile1.php');
    }
} else {
    $error = "Mauvais nom d'utilisateur ou mot de passe, veuillez réessayer";
    echo $error;
}

require_once '../views/connexion1.php';
