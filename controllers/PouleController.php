<?php

require_once __DIR__ . '/../models/ModelPoule.php';
require_once __DIR__ . '/../models/ModelTournoi.php';
require_once __DIR__ . '/../models/ModelEquipe.php';

class PouleController
{

    /**
     * Action lancée par l'admin pour générer le tirage
     */
    public function generer()
    {
        // 1. Sécurité : Vérifier si l'utilisateur est admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header("Location: index.php?action=accueil");
            exit();
        }

        $idTournoi = $_POST['id_tournoi'] ?? null;
        if (!$idTournoi) {
            header("Location: index.php?action=admin_panel");
            exit();
        }

        // 2. Récupérer les équipes via le ModelPoule
        $equipes = ModelPoule::getInscritsPourTirage($idTournoi);

        // 3. Vérification du quota (16 équipes)
        if (count($equipes) !== 16) {
            $_SESSION['flash_message_erreur'] = "Tirage impossible : il faut 16 équipes (actuellement : " . count($equipes) . ").";
            header("Location: index.php?action=admin_panel&sous_onglet=tournois");
            exit();
        }

        // 4. ALGORITHME DE TIRAGE
        shuffle($equipes); // Mélange aléatoire du tableau

        $lettresPoules = ['A', 'B', 'C', 'D'];
        $indexEquipe = 0;

        foreach ($lettresPoules as $lettre) {
            // On attribue 4 équipes par lettre
            for ($i = 0; $i < 4; $i++) {
                $idEquipe = $equipes[$indexEquipe]['id_equipe'];
                ModelPoule::assignerEquipeAPoule($idTournoi, $idEquipe, $lettre);
                $indexEquipe++;
            }
        }

        // 5. Mise à jour du statut du tournoi (via ModelTournoi)
        ModelTournoi::updateStatut($idTournoi, 'en_cours');

        $_SESSION['flash_message_succes'] = "Le tirage au sort a été effectué ! Les 4 poules sont prêtes.";
        header("Location: index.php?action=admin_panel&sous_onglet=tournois");
        exit();
    }
    /**
     * Affiche la page des poules pour un tournoi donné
     */
    public function voirPoules()
    {
        $idTournoi = $_GET['id_tournoi'] ?? null;

        if (!$idTournoi) {
            header("Location: index.php?action=tournoi");
            exit();
        }

        // 1. On récupère les infos du tournoi (nom, dates) via ModelTournoi
        $tournoi = ModelTournoi::getTournoiById($idTournoi);

        // 2. On récupère les poules organisées via notre ModelPoule
        $poules = ModelPoule::getPoulesComplet($idTournoi);

        // 3. On appelle la vue
        // Les variables $tournoi et $poules seront disponibles dans le fichier inclus
        require_once 'views/tabs/affichage_poules.php';
    }
}
