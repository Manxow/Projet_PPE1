<?php
// controllers/HomeController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class HomeController
{
    public function afficherAccueil()
    {
        $typePage = 'onglet';
        $fichierVue = 'tabs/accueil.php';
        $estConnecte = isset($_SESSION['joueur']);
        $action = 'accueil';
        require_once __DIR__ . '/../views/layout.php';
    }
}
