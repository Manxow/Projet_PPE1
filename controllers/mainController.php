<?php
// controllers/mainController.php

class mainController
{
    public function traiterRequete($action)
    {
        // Simulation rapide : on imagine que le joueur n'est pas connecté
        $estConnecte = isset($_SESSION['joueur']);

        // On liste nos pages
        $onglets = ['accueil', 'equipe', 'tournoi', 'statistiques'];
        $pagesAuth = ['connexion', 'inscription'];

        if (in_array($action, $onglets)) {
            $typePage = 'onglet';
            $fichierVue = "views/tabs/{$action}.php";
        } elseif (in_array($action, $pagesAuth)) {
            $typePage = 'auth';
            $fichierVue = "views/auth/{$action}.php";
        } else {
            // Sécurité : si on tape n'importe quoi dans l'URL, retour à l'accueil
            $action = 'accueil';
            $typePage = 'onglet';
            $fichierVue = "views/tabs/accueil.php";
        }

        // On charge le design principal (les variables ci-dessus y seront accessibles)
        require_once '../views/layout.php';
    }
}
