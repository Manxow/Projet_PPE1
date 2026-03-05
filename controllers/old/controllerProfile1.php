<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../models/ModelJoueur1.php';
require_once '../models/ModelEquipe1.php';

$listeEquipes = ModelEquipe::getAllEquipes();

require_once '../views/inscriptionjoueur1.php';

if ($_SESSION['joueur']->getIdJoueur()) {
    if (!empty($_POST)) {

        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $poste = $_POST['poste'];
        $niveau = $_POST['lvl'];
        $equipe = $_POST['team'];

        $_SESSION['joueur']->setNom($nom);
        $_SESSION['joueur']->setPrenom($prenom);
        $_SESSION['joueur']->setPoste($poste);
        $_SESSION['joueur']->setNiveau($lvl);
        $_SESSION['joueur']->setEquipe($equipe);
    }
} else {
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


        if ($estAjouté = 1) {
            header('Location: ../views/profile1.php');
        } else {
            $error = "Probleme de création du joueur";
            echo $error;
        }
    }
}
