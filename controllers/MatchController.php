<?php

require_once __DIR__ . '/../models/ModelMatch.php';
require_once __DIR__ . '/../models/ModelEquipe.php';
require_once __DIR__ . '/PhaseFinaleController.php';

class MatchController
{
    /**
     * Affiche le formulaire de saisie d'un résultat pour un match
     */
    public function afficherSaisieResultat()
    {
        if (!isset($_GET['id_match'])) {
            $_SESSION['flash_message_erreur'] = "Match non trouvé";
            header('Location: index.php?action=tournoi');
            exit;
        }

        $id_match = (int)$_GET['id_match'];
        $match = ModelMatch::getMatch($id_match);

        if (!$match) {
            $_SESSION['flash_message_erreur'] = "Ce match n'existe pas";
            header('Location: index.php?action=tournoi');
            exit;
        }

        // Vérifier que l'utilisateur est connecté et dans une équipe
        if (!isset($_SESSION['id_joueur']) || !isset($_SESSION['idTeam'])) {
            $_SESSION['flash_message_erreur'] = "Tu dois être connecté et faire partie d'une équipe";
            header('Location: index.php?action=connexion');
            exit;
        }

        $id_joueur = $_SESSION['id_joueur'];
        $id_equipe_joueur = $_SESSION['idTeam'];

        // Vérifier que le joueur est dans l'une des deux équipes du match
        if ($id_equipe_joueur != $match['id_equipe1'] && $id_equipe_joueur != $match['id_equipe2']) {
            $_SESSION['flash_message_erreur'] = "Tu n'es pas dans ce match";
            header('Location: index.php?action=tournoi');
            exit;
        }

        // Vérifier que le joueur est CAPITAINE de son équipe
        $equipeDuJoueur = ModelEquipe::getEquipeById($id_equipe_joueur);
        if (!$equipeDuJoueur || $equipeDuJoueur['id_createur'] != $id_joueur) {
            $_SESSION['flash_message_erreur'] = "Seul le capitaine peut saisir les résultats";
            header('Location: index.php?action=tournoi');
            exit;
        }

        // Vérifier que le match a une heure assignée
        if (!$match['date_match']) {
            $_SESSION['flash_message_erreur'] = "L'heure du match n'a pas encore été assignée";
            header('Location: index.php?action=tournoi');
            exit;
        }

        // Vérifier que match_time + 2h est passé
        $heure_debut_saisie = strtotime($match['date_match']) + (2 * 3600); // +2 heures
        $heure_maintenant = time();

        if ($heure_maintenant < $heure_debut_saisie) {
            $temps_attente = ceil(($heure_debut_saisie - $heure_maintenant) / 60); // en minutes
            $_SESSION['flash_message_erreur'] = "Le match commence à " . date('H:i', strtotime($match['date_match'])) . ". Vous pourrez saisir le résultat dans $temps_attente minutes.";
            header('Location: index.php?action=tournoi');
            exit;
        }

        $typePage = 'onglet';
        $fichierVue = 'match/saisie_resultat.php';
        $action = 'saisir_resultat';

        require_once __DIR__ . '/../views/layout.php';
    }

    /**
     * Traite la saisie d'un résultat
     */
    public function traiterSaisieResultat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=tournoi');
            exit;
        }

        if (!isset($_SESSION['id_joueur']) || !isset($_SESSION['idTeam'])) {
            $_SESSION['flash_message_erreur'] = "Tu dois être connecté";
            header('Location: index.php?action=connexion');
            exit;
        }

        $id_joueur = $_SESSION['id_joueur'];
        $id_equipe = $_SESSION['idTeam'];

        // Vérifier que c'est bien le capitaine
        $equipeDuJoueur = ModelEquipe::getEquipeById($id_equipe);
        if (!$equipeDuJoueur || $equipeDuJoueur['id_createur'] != $id_joueur) {
            $_SESSION['flash_message_erreur'] = "Seul le capitaine peut saisir les résultats";
            header('Location: index.php?action=tournoi');
            exit;
        }

        $id_match = (int)$_POST['id_match'];
        $buts_eq1 = (int)$_POST['buts_equipe1'];
        $buts_eq2 = (int)$_POST['buts_equipe2'];

        $match = ModelMatch::getMatch($id_match);
        if (!$match) {
            $_SESSION['flash_message_erreur'] = "Match introuvable";
            header('Location: index.php?action=tournoi');
            exit;
        }

        // Saisir le résultat
        $resultat = ModelMatch::saisirResultat($id_match, $id_equipe, $buts_eq1, $buts_eq2);

        if ($resultat['succes']) {
            $_SESSION['flash_message_succes'] = $resultat['message'];

            // Synchronise la table phase_finale et la progression du tableau (QF -> SF -> F).
            $syncFinales = (new PhaseFinaleController())->synchroniser((int)$match['id_tournoi']);
            if ($syncFinales['succes'] && !empty($syncFinales['message'])) {
                $_SESSION['flash_message_succes'] .= ' ' . $syncFinales['message'];
            }
        } else {
            $_SESSION['flash_message_erreur'] = $resultat['message'];
        }

        header('Location: index.php?action=tournoi');
        exit;
    }
}
