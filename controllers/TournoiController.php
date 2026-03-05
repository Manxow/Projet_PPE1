<?php
// controllers/TournoiController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class TournoiController
{
    public function afficherTournoi()
    {
        $typePage = 'onglet';
        $fichierVue = 'tabs/tournoi.php';
        $estConnecte = isset($_SESSION['joueur']);
        $action = 'tournoi';
        require_once __DIR__ . '/../views/layout.php';
    }
}
