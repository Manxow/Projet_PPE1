<?php
require_once __DIR__ . '/../models/ModelTournoi.php';
require_once __DIR__ . '/../models/ModelEquipe.php';

class TournoiController
{

    public function afficherTournois()
    {
        $tournois = ModelTournoi::getTournoisDisponibles();

        // On vérifie si l'utilisateur est capitaine pour afficher le bouton
        $estCapitaine = false;
        if (isset($_SESSION['id_joueur']) && isset($_SESSION['idTeam'])) {
            $equipe = ModelEquipe::getEquipeById($_SESSION['idTeam']);
            if ($equipe && $equipe['id_createur'] == $_SESSION['id_joueur']) {
                $estCapitaine = true;
            }
        }

        $typePage = 'onglet';
        $fichierVue = 'tabs/tournoi.php';
        $action = 'tournoi';
        require_once __DIR__ . '/../views/layout.php';
    }

    public function traiterInscription()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['idTeam'])) {
            $id_tournoi = $_POST['id_tournoi'];
            $id_equipe = $_SESSION['idTeam'];

            if (ModelTournoi::inscrireEquipe($id_equipe, $id_tournoi)) {
                $_SESSION['flash_message_succes'] = "Inscription réussie ! Votre équipe est prête pour le tournoi.";
            } else {
                $_SESSION['flash_message_erreur'] = "Erreur : Le tournoi est peut-être déjà complet.";
            }
        }
        header('Location: index.php?action=tournoi');
        exit;
    }
}
