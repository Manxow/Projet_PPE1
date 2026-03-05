<?php
// controllers/StatsController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class StatsController
{
    public function afficherStatistiques()
    {
        $typePage = 'onglet';
        $fichierVue = 'tabs/statistiques.php';
        $estConnecte = isset($_SESSION['joueur']);
        $action = 'statistiques';
        require_once __DIR__ . '/../views/layout.php';
    }
}
