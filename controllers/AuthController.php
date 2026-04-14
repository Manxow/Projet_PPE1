<?php

require_once __DIR__ . '/../models/ModelEquipe.php';
require_once __DIR__ . '/../models/ModelJoueur.php';

// controllers/AuthController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// à mettre à jour : les chemins d'inclusion des modèles et vues doivent être adaptés à votre structure de projet

class AuthController
{
    public function afficherInscription()
    {
        $listeEquipes = ModelEquipe::getAllEquipes();
        $typePage = 'auth';
        $fichierVue = 'auth/inscription.php';
        $estConnecte = isset($_SESSION['joueur']);
        require_once __DIR__ . '/../views/layout.php';
    }

    public function afficherConnexion()
    {
        $typePage = 'auth';
        $fichierVue = 'auth/connexion.php';
        $estConnecte = isset($_SESSION['joueur']);
        require_once __DIR__ . '/../views/layout.php';
    }

    public function deconnexion()
    {
        // 1. On s'assure que la session est bien démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. On vide toutes les variables de session
        session_unset();

        // 3. On détruit la session
        session_destroy();

        // 4. On redirige vers l'accueil
        header('Location: index.php?action=accueil');
        exit();
    }

    public function traiterInscription()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=inscription');
            exit;
        }

        // 1. On récupère TOUTES les données du formulaire dès le début
        $identifiant = $_POST['user'] ?? '';
        $mdp = $_POST['pw'] ?? '';
        $verif_mdp = $_POST['confirm_pw'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $poste = $_POST['poste'] ?? '';
        $niveau = $_POST['lvl'] ?? '';


        // 2. NOUVEAU : On sauvegarde les champs (SAUF les mots de passe) dans la session
        // au cas où on devrait recharger la page avec une erreur.
        $_SESSION['form_data'] = [
            'user' => $identifiant,
            'nom' => $nom,
            'prenom' => $prenom,
            'poste' => $poste,
            'lvl' => $niveau,
        ];

        // --- NOUVEAU : Vérification de la robustesse du mot de passe ---
        $longueurOk = strlen($mdp) >= 8;
        $majusculeOk = preg_match('/[A-Z]/', $mdp); // Au moins une lettre de A à Z
        $minusculeOk = preg_match('/[a-z]/', $mdp); // Au moins une lettre de a à z
        $chiffreOk = preg_match('/[0-9]/', $mdp); // NOUVEAU : Au moins un chiffre
        $specialOk = preg_match('/[^a-zA-Z0-9]/', $mdp); // Au moins un caractère qui n'est ni lettre ni chiffre

        // Si une seule des règles n'est pas respectée
        if (!$longueurOk || !$majusculeOk || !$minusculeOk || !$chiffreOk || !$specialOk) {
            // On sauvegarde l'état de chaque règle dans un tableau en session
            $_SESSION['erreurs_mdp'] = [
                'longueur' => $longueurOk,
                'majuscule' => $majusculeOk,
                'minuscule' => $minusculeOk,
                'chiffre' => $chiffreOk, // On l'ajoute à la session
                'special' => $specialOk
            ];
            $_SESSION['flash_message_erreur'] = "Le mot de passe ne respecte pas les critères de sécurité.";
            header('Location: index.php?action=inscription');
            exit;
        }
        // ----------------------------------------------------------------

        if ($mdp !== $verif_mdp) {
            $_SESSION['flash_message_erreur'] = "Les mots de passe ne correspondent pas.";
            header('Location: index.php?action=inscription');
            exit;
        }



        $hashed_mdp = password_hash($mdp, PASSWORD_DEFAULT);


        $joueur = new ModelJoueur($identifiant, $hashed_mdp, $nom, $prenom, $poste, $niveau, $equipe = null);

        $estAjoute = $joueur->AjouterJoueur();


        if ($estAjoute == 1) {
            $monId = $joueur->getMonId();
            // $joueur->setId($monId);
            // $_SESSION['joueur'] = $joueur;
            $_SESSION['id_joueur'] = $monId;

            // NOUVEAU : On garde son pseudo en mémoire
            $_SESSION['pseudo_joueur'] = $identifiant;

            // $_SESSION['idTeam'] = $equipe;
            // $equipeDuJoueur = new ModelEquipe($equipe);
            // $_SESSION['nomTeam'] = $equipeDuJoueur->getNomEquipe();

            // NOUVEAU : Un nouveau joueur n'est jamais admin
            $_SESSION['is_admin'] = 0;

            $_SESSION['flash_message_succes'] = "Bienvenue au centre Five M2L ! Ton compte a bien été créé.";
            header('Location: index.php?action=profile');
            exit;
        } else {
            $_SESSION['flash_message_erreur'] = "Une erreur est survenue lors de la création du compte.";
            header('Location: index.php?action=inscription');
            exit;
        }
    }

    public function traiterConnexion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=connexion');
            exit;
        }

        $pseudo = $_POST['user'] ?? '';
        $mdp = $_POST['pw'] ?? '';
        // Verify credentials against joueur table
        $player = ModelJoueur::getJoueurByPseudo($pseudo);
        if ($player && password_verify($mdp, $player['pw'])) {

            // Régénérer l'ID de session pour éviter les vieilles sessions
            session_regenerate_id(true);

            // SUCCÈS : On connecte le joueur
            $_SESSION['id_joueur'] = $player['id_joueur'];

            // NOUVEAU : On garde son pseudo en mémoire pour le Header !
            $_SESSION['pseudo_joueur'] = $pseudo;

            // NOUVEAU : On sauvegarde son statut (0 = Joueur, 1 = Admin)
            $_SESSION['is_admin'] = $player['is_admin'];
            $_SESSION['idTeam'] = $player['id_equipe'];
            $_SESSION['nomTeam'] = $player['id_equipe'] ? ModelEquipe::getNomEquipe($player['id_equipe']) : null;

            // On nettoie le pseudo temporaire
            unset($_SESSION['nom_utilisateur']);


            header('Location: index.php?action=profile');
            exit;
        } else {
            // ERREUR : Mauvais identifiants
            $_SESSION['flash_message_erreur'] = "Mauvais nom d'utilisateur ou mot de passe, veuillez réessayer.";

            // On le renvoie sur la page de connexion
            header('Location: index.php?action=connexion');
            exit;
        }
    }
}
