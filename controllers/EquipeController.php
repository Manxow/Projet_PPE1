<?php
// controllers/EquipeController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class EquipeController
{
    public function afficherEquipe()
    {
        $typePage = 'onglet';
        $fichierVue = 'tabs/equipe.php';
        $estConnecte = isset($_SESSION['joueur']);
        $action = 'equipe';
        require_once __DIR__ . '/../views/layout.php';
    }
}
