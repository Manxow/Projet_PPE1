<?php
require_once __DIR__ . '/../models/ModelTournoi.php';
require_once __DIR__ . '/../models/ModelEquipe.php';
require_once __DIR__ . '/../models/ModelPoule.php';
require_once __DIR__ . '/../models/ModelMatch.php';
require_once __DIR__ . '/../models/ModelPhaseFinale.php';

class TournoiController
{
    public function afficherTournois()
    {
        // 1. On récupère la liste basique des tournois
        $tournois = ModelTournoi::getTournoisDisponibles();

        // 🎯 LE NOUVEAU BLOC EST ICI : 
        // On parcourt la liste et on ajoute les poules pour les tournois en cours
        foreach ($tournois as &$t) {
            if ($t['statut'] === 'en_cours') {
                $t['poules'] = ModelPoule::getPoulesComplet($t['id_tournoi']);
                $t['classements'] = ModelMatch::getClassementsTournoi($t['id_tournoi']);
                $t['phases_finales'] = ModelPhaseFinale::getPhasesFinalesTournoi($t['id_tournoi']);
            } elseif ($t['statut'] !== 'termine') {
                // Avant le lancement (ouvert/complet/a_venir), on montre les participants.
                $t['participants'] = ModelTournoi::getParticipantsTournoi($t['id_tournoi']);
            }
        }
        unset($t); // Bonne pratique de sécurité en PHP après un foreach avec un "&"

        // Tournois terminés : on récupère l'historique + classement final
        $tournoisTermines = ModelTournoi::getTournoisTermines();
        foreach ($tournoisTermines as &$t) {
            $t['poules']          = ModelPoule::getPoulesComplet($t['id_tournoi']);
            $t['classements']     = ModelMatch::getClassementsTournoi($t['id_tournoi']);
            $t['phases_finales']  = ModelPhaseFinale::getPhasesFinalesTournoi($t['id_tournoi']);
            $t['classement_final'] = ModelPhaseFinale::getClassementFinal($t['id_tournoi']);
        }
        unset($t);

        // 2. On vérifie si l'utilisateur est capitaine pour afficher le bouton
        $estCapitaine = false;
        if (isset($_SESSION['id_joueur']) && isset($_SESSION['idTeam'])) {
            $equipe = ModelEquipe::getEquipeById($_SESSION['idTeam']);
            if ($equipe && $equipe['id_createur'] == $_SESSION['id_joueur']) {
                $estCapitaine = true;
            }
        }

        // 3. On envoie tout à la vue
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
