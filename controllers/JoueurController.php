<?php
// controllers/JoueurController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/ModelJoueur1.php';
require_once __DIR__ . '/../models/ModelEquipe1.php';

class JoueurController
{
    public function afficherInscriptionJoueur()
    {
        $listeEquipes = ModelEquipe::getAllEquipes();
        $typePage = 'auth';
        $fichierVue = 'views/inscriptionjoueur1.php';
        $estConnecte = isset($_SESSION['joueur']);
        require_once __DIR__ . '/../views/layout.php';
    }

    public function traiterInscriptionJoueur()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=inscription_joueur');
            exit;
        }

        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $poste = $_POST['poste'] ?? '';
        $niveau = $_POST['lvl'] ?? '';
        $equipe = $_POST['team'] ?? null;

        // include credentials collected earlier during inscription
        $nom_utilisateur = $_SESSION['nom_utilisateur'] ?? null;
        $pw = $_SESSION['pw'] ?? null;

        $joueur = new ModelJoueur($nom, $prenom, $poste, $niveau, $equipe, $nom_utilisateur, $pw);
        $estAjoute = $joueur->AjouterJoueur();
        $_SESSION['joueur'] = $joueur;

        $_SESSION['idTeam'] = $equipe;
        $equipeDuJoueur = new ModelEquipe($equipe);
        $nomTeam = $equipeDuJoueur->getNomEquipe();
        $_SESSION['nomTeam'] = $nomTeam;

        if ($estAjoute == 1) {
            header('Location: index.php?action=profile');
            exit;
        } else {
            echo "Probleme de création du joueur";
        }
    }

    public function afficherProfile()
    {
        if (!isset($_SESSION['joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $listeEquipes = ModelEquipe::getAllEquipes();
        $typePage = 'onglet';
        $fichierVue = 'views/profile.php';
        $estConnecte = isset($_SESSION['joueur']);
        require_once __DIR__ . '/../views/layout.php';
    }

    public function traiterProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }

        if (!isset($_SESSION['joueur'])) {
            header('Location: index.php?action=connexion');
            exit;
        }

        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $poste = $_POST['poste'] ?? '';
        $niveau = $_POST['lvl'] ?? '';
        $equipe = $_POST['team'] ?? null;

        $_SESSION['joueur']->setNom($nom);
        $_SESSION['joueur']->setPrenom($prenom);
        $_SESSION['joueur']->setPoste($poste);
        $_SESSION['joueur']->setNiveau($niveau);
        $_SESSION['joueur']->setEquipe($equipe);

        header('Location: index.php?action=profile');
        exit;
    }
}
