<?php
// controllers/AuthController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// à mettre à jour : les chemins d'inclusion des modèles et vues doivent être adaptés à votre structure de projet
require_once __DIR__ . '/../models/ModelEquipe1.php';
require_once __DIR__ . '/../models/ModelJoueur1.php';

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

    public function traiterInscription()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=inscription');
            exit;
        }

        $identifiant = $_POST['user'] ?? '';
        $mdp = $_POST['pw'] ?? '';
        // Store credentials in session and continue to player details
        $_SESSION['nom_utilisateur'] = $identifiant;
        $_SESSION['pw'] = $mdp;
        header('Location: index.php?action=inscription_joueur');
        exit;
    }

    public function traiterConnexion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=connexion');
            exit;
        }

        $identifiant = $_POST['user'] ?? '';
        $mdp = $_POST['pw'] ?? '';

        $_SESSION['nom_utilisateur'] = $identifiant;
        // Verify credentials against joueur table
        $player = ModelJoueur::getByCredentials($identifiant, $mdp);
        if ($player) {
            $monJoueur = new ModelJoueur($player['nom'], $player['prenom'], $player['poste'], $player['niveau'], $player['id_equipe'], $player['nom_utilisateur'], $player['pw']);
            $_SESSION['joueur'] = $monJoueur;

            $equipeDuJoueur = new ModelEquipe($player['id_equipe']);
            $nomTeam = $equipeDuJoueur->getNomEquipe();
            $_SESSION['nomTeam'] = $nomTeam;

            header('Location: index.php?action=profile');
            exit;
        } else {
            echo "Mauvais nom d'utilisateur ou mot de passe, veuillez réessayer";
        }
    }
}
