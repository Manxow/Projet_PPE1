<?php
// controllers/HomeController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/ModelJoueur.php';

class HomeController
{
    public function afficherAccueil()
    {
        // On vérifie la connexion avec la bonne variable de session
        $estConnecte = isset($_SESSION['id_joueur']);
        $aUneEquipe = false;

        // Si le joueur est connecté, on vérifie s'il a une équipe
        if ($estConnecte) {
            $id_joueur = $_SESSION['id_joueur'];
            $joueur = ModelJoueur::getJoueurById($id_joueur);

            if ($joueur && $joueur['id_equipe'] !== null) {
                $aUneEquipe = true;
            }
        }

        $typePage = 'onglet';
        $fichierVue = 'tabs/accueil.php';
        $action = 'accueil';

        require_once __DIR__ . '/../views/layout.php';
    }
}
