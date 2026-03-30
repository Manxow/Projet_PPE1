<?php
// controllers/JoueurController.php
require_once __DIR__ . '/../models/ModelJoueur.php';
require_once __DIR__ . '/../models/ModelEquipe.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class JoueurController
{
    public function afficherInscriptionJoueur() //OLD
    {
        $listeEquipes = ModelEquipe::getAllEquipes();
        $typePage = 'auth';
        $fichierVue = 'views/inscriptionjoueur.php';
        $estConnecte = isset($_SESSION['joueur']);
        require_once __DIR__ . '/../views/layout.php';
    }


    public function afficherProfile()
    {
        /* if (!isset($_SESSION['id_joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }*/

        $joueurActuel = ModelJoueur::getJoueurById($_SESSION['id_joueur']);


        $monJoueur = new ModelJoueur($joueurActuel['user'], $joueurActuel['pw'], $joueurActuel['nom'], $joueurActuel['prenom'], $joueurActuel['poste'], $joueurActuel['niveau'], $joueurActuel['id_equipe']);
        $listeEquipes = ModelEquipe::getAllEquipes();
        $equipeId = $monJoueur->getEquipe();
        if ($equipeId != null) {
            $equipeDuJoueur = new ModelEquipe($equipeId);
            $nomTeam = $equipeDuJoueur->getNomEquipe($equipeId);
        }

        $typePage = 'onglet';
        $fichierVue = 'tabs/profile.php';
        $estConnecte = isset($_SESSION['id_joueur']);
        $action = 'profile';
        require_once __DIR__ . '/../views/layout.php';
    }



    public function traiterProfile()
    {
        // 1. Vérification de la méthode et de la connexion
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }

        if (!isset($_SESSION['id_joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $id = $_SESSION['id_joueur'];
        $messageSucces = ""; // On prépare un message de succès cumulable

        // ==========================================
        // PARTIE 1 : GESTION DU MOT DE PASSE
        // ==========================================
        $oldpw = $_POST['pw'] ?? '';
        $newpw = $_POST['new_pw'] ?? '';
        $confirmpw = $_POST['confirm_pw'] ?? '';

        // Si l'utilisateur tente de changer son mot de passe
        if (!empty($oldpw) || !empty($newpw) || !empty($confirmpw)) {


            if (empty($newpw)) {
                $_SESSION['flash_message_erreur'] = "Veuillez renseigner un nouveau mot de passe.";
                header('Location: index.php?action=profile');
                exit;
            }

            $currentHash = ModelJoueur::getPwById($id);

            // Vérification sécurisée !
            if (!password_verify($oldpw, $currentHash)) {
                $_SESSION['flash_message_erreur'] = "L'ancien mot de passe est incorrect.";
                header('Location: index.php?action=profile');
                exit;
            }

            // Le nouveau mot de passe est-il différent de l'ancien ?
            if ($oldpw === $newpw) {
                $_SESSION['flash_message_erreur'] = "Le nouveau mot de passe doit être différent de l'ancien.";
                header('Location: index.php?action=profile');
                exit;
            }

            $longueurOk = strlen($newpw) >= 8;
            $majusculeOk = preg_match('/[A-Z]/', $newpw);
            $minusculeOk = preg_match('/[a-z]/', $newpw);
            $chiffreOk = preg_match('/[0-9]/', $newpw);
            $specialOk = preg_match('/[^a-zA-Z0-9]/', $newpw);

            if (!$longueurOk || !$majusculeOk || !$minusculeOk || !$chiffreOk || !$specialOk) {
                $_SESSION['erreurs_mdp'] = [
                    'longueur' => $longueurOk,
                    'majuscule' => $majusculeOk,
                    'minuscule' => $minusculeOk,
                    'chiffre' => $chiffreOk,
                    'special' => $specialOk
                ];
                $_SESSION['flash_message_erreur'] = "Le nouveau mot de passe ne respecte pas les critères de sécurité.";
                header('Location: index.php?action=profile');
                exit;
            }

            if ($newpw !== $confirmpw) {
                $_SESSION['flash_message_erreur'] = "Les nouveaux mots de passe ne correspondent pas.";
                header('Location: index.php?action=profile');
                exit;
            }

            // Mise à jour du mot de passe
            $hashedPw = password_hash($newpw, PASSWORD_DEFAULT);
            ModelJoueur::updatePassword($id, $hashedPw);
            $messageSucces .= "Mot de passe modifié avec succès. ";
        }

        // ==========================================
        // PARTIE 2 : GESTION DU PROFIL CLASSIQUE
        // ==========================================
        $user = $_POST['user'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $poste = $_POST['poste'] ?? '';
        $niveau = $_POST['lvl'] ?? '';
        // Attention : on s'assure que si la valeur est vide (""), elle devient bien NULL pour la base de données
        $equipe = !empty($_POST['team']) ? $_POST['team'] : null;
        $code_saisi = trim($_POST['code_equipe'] ?? '');

        // --- NOUVEAU : VÉRIFICATION DU CHANGEMENT D'ÉQUIPE ---
        // 1. On récupère les infos actuelles du joueur AVANT la modification
        $joueurActuel = ModelJoueur::getJoueurById($id);
        $ancienne_equipe = $joueurActuel['id_equipe'] ?? null;

        // 2. Si la nouvelle équipe est différente de l'ancienne ET qu'il n'a pas choisi "Sans équipe"
        if ($equipe !== $ancienne_equipe && $equipe !== null) {

            // On va chercher le vrai code de la nouvelle équipe
            $vraiCode = ModelEquipe::getCodeAcces($equipe);

            // On compare
            if ($code_saisi !== $vraiCode) {
                $_SESSION['flash_message_erreur'] = "Le code d'accès pour la nouvelle équipe est incorrect. Aucune modification n'a été enregistrée.";
                header('Location: index.php?action=profile');
                exit;
            }
        }
        // -----------------------------------------------------
        // Mise à jour en BDD
        ModelJoueur::updateJoueur($id, $user, $nom, $prenom, $poste, $niveau, $equipe);
        $messageSucces .= "Profil mis à jour.";

        // NOUVEAU : On met à jour le pseudo dans la session au cas où il l'aurait modifié
        $_SESSION['pseudo_joueur'] = $user;

        // Mise à jour du cache de l'équipe en session
        if ($equipe) {
            $_SESSION['idTeam'] = $equipe;
            $equipeDuJoueur = new ModelEquipe($equipe);
            $_SESSION['nomTeam'] = $equipeDuJoueur->getNomEquipe($equipe);
        }

        // ==========================================
        // PARTIE 3 : REDIRECTION ET MESSAGES
        // ==========================================
        $_SESSION['flash_message_succes'] = trim($messageSucces);
        header('Location: index.php?action=profile');
        exit;
    }
}
