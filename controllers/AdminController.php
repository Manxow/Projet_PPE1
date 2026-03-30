<?php
// controllers/AdminController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/ModelEquipe.php';

class AdminController
{
    // Sécurité stricte : on vérifie que c'est bien un admin
    private function verifierAdmin()
    {
        if (!isset($_SESSION['id_joueur']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header('Location: index.php?action=accueil');
            exit;
        }
    }

    // Affichage de la vue
    public function afficherPanel()
    {
        $this->verifierAdmin();

        // On gère les sous-onglets (par défaut 'attente')
        $sous_onglet = $_GET['sous_onglet'] ?? 'attente';

        // On récupère les données selon l'onglet
        $equipesEnAttente = ModelEquipe::getEquipesEnAttente();
        $equipesValidees = ModelEquipe::getEquipesValidees();

        // NOUVEAU : On récupère aussi les tournois pour l'onglet gestion
        require_once __DIR__ . '/../models/ModelTournoi.php';
        $listeTournois = ModelTournoi::getTournoisDisponibles();

        $typePage = 'onglet';
        $fichierVue = 'tabs/admin.php';
        $action = 'admin_panel';

        require_once __DIR__ . '/../views/layout.php';
    }

    // Créer un tournoi

    public function traiterCreerTournoi()
    {
        $this->verifierAdmin(); // Sécurité : on vérifie que c'est bien l'admin

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_tournoi']);
            $debut = $_POST['date_debut'];
            $fin = $_POST['date_fin'];

            if (!empty($nom) && !empty($debut) && !empty($fin)) {

                // --- RÈGLES MÉTIER SUR LES DATES ---
                $aujourdhui = date('Y-m-d'); // Date actuelle au format AAAA-MM-JJ

                // Règle 1 : La date de début ne peut pas être dans le passé
                if ($debut < $aujourdhui) {
                    $_SESSION['flash_message_erreur'] = "Erreur : La date de début ne peut pas être antérieure à aujourd'hui.";
                }
                // Règle 2 : La date de fin ne peut pas être avant le début
                elseif ($fin < $debut) {
                    $_SESSION['flash_message_erreur'] = "Erreur : La date de fin ne peut pas être antérieure à la date de début.";
                }
                // Tout est valide : on crée le tournoi
                else {
                    require_once __DIR__ . '/../models/ModelTournoi.php';
                    ModelTournoi::creerTournoi($nom, $debut, $fin);
                    $_SESSION['flash_message_succes'] = "Le tournoi '$nom' a été créé avec succès !";
                }
            } else {
                $_SESSION['flash_message_erreur'] = "Veuillez remplir tous les champs.";
            }
        }

        header('Location: index.php?action=admin_panel&sous_onglet=tournois');
        exit;
    }

    // Traiter la suppression d'un tournoi

    public function traiterSupprimerTournoi()
    {
        $this->verifierAdmin(); // Sécurité : on vérifie que c'est bien l'admin

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tournoi'])) {
            $id_tournoi = $_POST['id_tournoi'];
            require_once __DIR__ . '/../models/ModelTournoi.php';

            if (ModelTournoi::supprimerTournoi($id_tournoi)) {
                $_SESSION['flash_message_succes'] = "Le tournoi a été supprimé avec succès.";
            } else {
                $_SESSION['flash_message_erreur'] = "Erreur lors de la suppression du tournoi.";
            }
        }

        // --- REDIRECTION INTELLIGENTE ---
        $provenance = $_POST['provenance'] ?? 'tournoi';
        if ($provenance === 'admin_panel') {
            header('Location: index.php?action=admin_panel&sous_onglet=tournois');
        } else {
            header('Location: index.php?action=tournoi');
        }
        exit;
    }

    // Traitement : Accepter une équipe
    public function accepterEquipe()
    {
        $this->verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_equipe'])) {
            ModelEquipe::validerEquipe($_POST['id_equipe']);
            $_SESSION['flash_message_succes'] = "L'équipe a bien été validée et intégrée au tournoi !";
        }

        header('Location: index.php?action=admin_panel&sous_onglet=attente');
        exit;
    }

    // Traitement : Refuser une équipe
    public function refuserEquipe()
    {
        $this->verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_equipe'])) {
            ModelEquipe::refuserEquipe($_POST['id_equipe']);
            $_SESSION['flash_message_succes'] = "L'équipe a été refusée et supprimée. Les joueurs sont libres.";
        }

        header('Location: index.php?action=admin_panel&sous_onglet=attente');
        exit;
    }


    // Traite la soumission du formulaire d'édition
    public function traiterEditerTournoi()
    {
        // $this->verifierAdmin(); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_tournoi = $_POST['id_tournoi'] ?? null;
            $nom = trim($_POST['nom_tournoi']);
            $debut = $_POST['date_debut'];
            $fin = $_POST['date_fin'];

            if ($id_tournoi && !empty($nom) && !empty($debut) && !empty($fin)) {
                require_once __DIR__ . '/../models/ModelTournoi.php';
                ModelTournoi::modifierTournoi($id_tournoi, $nom, $debut, $fin);

                $_SESSION['flash_message_succes'] = "Le tournoi a été modifié avec succès.";
                header('Location: index.php?action=tournoi');
                exit;
            } else {
                $_SESSION['flash_message_erreur'] = "Veuillez remplir tous les champs.";
                header('Location: index.php?action=admin_editer_tournoi&id=' . $id_tournoi);
                exit;
            }
        }
    }

    // Traite la soumission de la modification d'une équipe (NOM)
    public function traiterEditerEquipe()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_equipe = $_POST['id_equipe'] ?? null;
            $nom = trim($_POST['nom_equipe']);

            if ($id_equipe && !empty($nom)) {
                require_once __DIR__ . '/../models/ModelEquipe.php';
                ModelEquipe::modifierEquipe($id_equipe, $nom);
                $_SESSION['flash_message_succes'] = "L'équipe a été renommée avec succès.";
            } else {
                $_SESSION['flash_message_erreur'] = "Le nom de l'équipe ne peut pas être vide.";
            }

            // --- REDIRECTION INTELLIGENTE ---
            $provenance = $_POST['provenance'] ?? 'equipe';

            if ($provenance === 'admin_panel') {
                header('Location: index.php?action=admin_panel&sous_onglet=validees');
            } else {
                header('Location: index.php?action=equipe&sous_onglet=liste');
            }
            exit;
        }
    }

    //Suppression d'une équipe par ADMIN

    public function traiterSupprimerEquipe()
    {
        $this->verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_equipe'])) {
            $id_equipe = $_POST['id_equipe'];

            require_once __DIR__ . '/../models/ModelEquipe.php';

            if (ModelEquipe::refuserEquipe($id_equipe)) {
                $_SESSION['flash_message_succes'] = "L'équipe a été supprimée et les joueurs ont été libérés.";
            } else {
                $_SESSION['flash_message_erreur'] = "Erreur lors de la suppression de l'équipe.";
            }
        }

        // Redirection intelligente : on regarde d'où vient l'admin
        $provenance = $_POST['provenance'] ?? 'equipe';
        if ($provenance === 'admin_panel') {
            header('Location: index.php?action=admin_panel&sous_onglet=validees');
        } else {
            header('Location: index.php?action=equipe&sous_onglet=liste');
        }
        exit;
    }

    public function genererPoules()
    {
        $idTournoi = $_POST['id_tournoi'] ?? null;

        // 1. On récupère les équipes
        $equipes = ModelTournoi::getEquipesInscrites($idTournoi);

        if (count($equipes) != 16) {
            $_SESSION['flash_message_erreur'] = "Il faut exactement 16 équipes pour lancer le tirage !";
            header("Location: index.php?action=admin_panel&sous_onglet=tournois");
            exit();
        }

        // 2. LE TIRAGE AU SORT (Magie de PHP)
        shuffle($equipes); // Mélange l'ordre du tableau aléatoirement

        // 3. RÉPARTITION
        $poules = ['A', 'B', 'C', 'D'];
        $compteur = 0;

        foreach ($poules as $lettre) {
            for ($i = 0; $i < 4; $i++) {
                $idEquipe = $equipes[$compteur]['id_equipe'];
                ModelTournoi::majPouleEquipe($idTournoi, $idEquipe, $lettre);
                $compteur++;
            }
        }

        // 4. On change le statut du tournoi
        ModelTournoi::updateStatut($idTournoi, 'en_cours');

        $_SESSION['flash_message_succes'] = "Tirage effectué avec succès !";
        header("Location: index.php?action=admin_panel&sous_onglet=tournois");
    }
}
