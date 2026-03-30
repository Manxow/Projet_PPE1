<?php
// controllers/EquipeController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/ModelJoueur.php';
require_once __DIR__ . '/../models/ModelEquipe.php';

class EquipeController
{
    public function afficherEquipe()
    {
        $estConnecte = isset($_SESSION['id_joueur']);
        $aUneEquipe = false;
        $statutEquipe = ''; // NOUVEAU : On prépare la variable pour la vue

        $listeEquipesValidees = ModelEquipe::getEquipesValidees();

        // On capte le sous-onglet demandé dans l'URL (par défaut : 'liste')
        $sous_onglet = $_GET['sous_onglet'] ?? 'liste';

        if ($estConnecte) {
            $id_joueur = $_SESSION['id_joueur'];
            $joueur = ModelJoueur::getJoueurById($id_joueur);
            $id_equipe = $joueur['id_equipe'] ?? null;

            if ($id_equipe !== null) {
                $aUneEquipe = true;
                $equipeDuJoueur = ModelEquipe::getEquipeById($id_equipe);

                if ($equipeDuJoueur) {
                    $nomDeMonEquipe = $equipeDuJoueur['nom'];
                    $codeAccesEquipe = $equipeDuJoueur['code_acces'];
                    $idCapitaine = $equipeDuJoueur['id_createur'];

                    // NOUVEAU : On récupère le statut de l'équipe (en_attente ou valide)
                    $statutEquipe = $equipeDuJoueur['statut'];
                } else {
                    $nomDeMonEquipe = 'Équipe introuvable';
                }
            }
        }

        $typePage = 'onglet';
        $fichierVue = 'tabs/equipe.php';
        $action = 'equipe';

        require_once __DIR__ . '/../views/layout.php';
    }

    public function traiterCreerEquipe()
    {
        // 1. Vérifications de base (POST + Connexion)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=equipe');
            exit;
        }

        if (!isset($_SESSION['id_joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $id_joueur = $_SESSION['id_joueur'];

        // --- 🔒 NOUVEAU VERROU DE SÉCURITÉ ---
        // On s'assure qu'il n'a pas DÉJÀ une équipe avant de le laisser en créer une !
        require_once __DIR__ . '/../models/ModelJoueur.php';
        $joueurVerif = ModelJoueur::getJoueurById($id_joueur);
        if ($joueurVerif['id_equipe'] !== null) {
            $_SESSION['flash_message_erreur'] = "Tricheur ! Tu appartiens déjà à une équipe.";
            header('Location: index.php?action=equipe');
            exit;
        }

        // 2. On récupère les données
        $nom_equipe = trim($_POST['nom_equipe'] ?? '');
        $nouveau_code = trim($_POST['nouveau_code'] ?? '');

        // 3. Sécurité : vérifier que les champs ne sont pas vides
        if (empty($nom_equipe) || empty($nouveau_code)) {
            $_SESSION['flash_message_erreur'] = "Le nom et le code d'accès sont obligatoires.";
            header('Location: index.php?action=equipe&sous_onglet=creer');
            exit;
        }

        // 4. On crée l'équipe en base de données et on récupère son tout nouvel ID
        $id_nouvelle_equipe = ModelEquipe::creerEquipe($nom_equipe, $nouveau_code, $id_joueur);

        // 5. On met à jour le joueur pour l'intégrer automatiquement à SON équipe !
        // (On réutilise la super méthode qu'on a faite tout à l'heure)
        require_once __DIR__ . '/../models/ModelJoueur.php';
        ModelJoueur::updateEquipeJoueur($id_joueur, $id_nouvelle_equipe);

        // 6. On met à jour la session
        $_SESSION['idTeam'] = $id_nouvelle_equipe;
        $_SESSION['nomTeam'] = $nom_equipe;

        // 7. On redirige avec les félicitations
        $_SESSION['flash_message_succes'] = "Félicitations Capitaine ! Ton équipe '$nom_equipe' a été créée. Elle est en attente de validation par l'administrateur.";
        header('Location: index.php?action=equipe');
        exit;
    }

    //ANNULER LA CREATION D'UNE EQUIPE (SEULEMENT SI ON EST LE CREATEUR ET QUE L'EQUIPE N'EST PAS ENCORE VALIDEE)

    public function traiterAnnulerEquipe()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=equipe');
            exit;
        }

        if (!isset($_SESSION['id_joueur']) || !isset($_SESSION['idTeam'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $id_equipe = $_SESSION['idTeam'];
        $id_joueur = $_SESSION['id_joueur'];

        // 1. Sécurité : on vérifie que l'équipe existe et que le joueur en est bien le créateur (capitaine)
        $infoEquipe = ModelEquipe::getEquipeById($id_equipe);

        if ($infoEquipe && $infoEquipe['id_createur'] == $id_joueur && $infoEquipe['statut'] === 'en_attente') {

            // 2. On réutilise la méthode de l'admin pour dissoudre l'équipe proprement !
            ModelEquipe::refuserEquipe($id_equipe);

            // 3. On nettoie la session du joueur pour qu'il redevienne "Sans équipe"
            unset($_SESSION['idTeam']);
            unset($_SESSION['nomTeam']);

            $_SESSION['flash_message_succes'] = "Ta demande de création a bien été annulée et l'équipe dissoute.";
        } else {
            $_SESSION['flash_message_erreur'] = "Impossible d'annuler cette équipe (seul le capitaine peut le faire, et l'équipe doit être en attente).";
        }

        header('Location: index.php?action=equipe');
        exit;
    }

    public function traiterRejoindreEquipe()
    {
        // 1. On vérifie que la méthode est bien POST et que le joueur est connecté
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=equipe');
            exit;
        }

        if (!isset($_SESSION['id_joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        // 2. On récupère les données du formulaire
        $id_joueur = $_SESSION['id_joueur'];
        $id_equipe = $_POST['id_equipe'] ?? null;
        $code_saisi = trim($_POST['code_acces'] ?? '');

        // 3. Sécurité : vérifier que les champs ne sont pas vides
        if (empty($id_equipe) || empty($code_saisi)) {
            $_SESSION['flash_message_erreur'] = "Veuillez sélectionner une équipe et saisir le code d'accès.";
            // On le renvoie sur l'onglet "rejoindre"
            header('Location: index.php?action=equipe&sous_onglet=rejoindre');
            exit;
        }

        // 4. On va chercher le vrai code de l'équipe dans la base de données
        $vraiCode = ModelEquipe::getCodeAcces($id_equipe);

        // 5. La vérification !
        if ($code_saisi !== $vraiCode) {
            $_SESSION['flash_message_erreur'] = "Le code d'accès est incorrect. Demande-le au capitaine de l'équipe !";
            header('Location: index.php?action=equipe&sous_onglet=rejoindre');
            exit;
        }

        // 6. Si on arrive ici, c'est que le code est BON ! On met à jour le joueur.
        ModelJoueur::updateEquipeJoueur($id_joueur, $id_equipe);

        // On récupère le nom de la nouvelle équipe pour le message de succès
        $nomEquipeRejointe = ModelEquipe::getNomEquipe($id_equipe);

        // On met à jour les infos en session pour un affichage fluide
        $_SESSION['idTeam'] = $id_equipe;
        $_SESSION['nomTeam'] = $nomEquipeRejointe; // On le stocke en session, c'est très pratique !

        // 7. On félicite le joueur de manière personnalisée
        $_SESSION['flash_message_succes'] = "Félicitations ! Tu as bien rejoint l'équipe '" . htmlspecialchars($nomEquipeRejointe) . "'.";

        header('Location: index.php?action=equipe');
        exit;
    }

    public function traiterQuitterEquipe()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }

        if (!isset($_SESSION['id_joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $id_joueur = $_SESSION['id_joueur'];

        // On détache le joueur de son équipe en base de données
        ModelJoueur::quitterEquipe($id_joueur);

        // On n'oublie pas de vider sa session pour que l'affichage se mette à jour !
        unset($_SESSION['idTeam']);
        unset($_SESSION['nomTeam']);

        $_SESSION['flash_message_succes'] = "Tu as bien quitté l'équipe.";
        header('Location: index.php?action=profile');
        exit;
    }
}
