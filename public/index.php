<?php
// public/index.php
session_start();

// On récupère l'action demandée (par défaut 'accueil')
$action = $_GET['action'] ?? 'accueil';

// --- LE ROUTEUR (Switch) ---
switch ($action) {

    // ------------------------------------
    // ZONE PUBLIQUE (Accueil)
    // ------------------------------------
    case 'accueil':
        require_once __DIR__ . '/../controllers/HomeController.php';
        $controller = new HomeController();
        $controller->afficherAccueil();
        break;

    // ------------------------------------
    // ZONE AUTHENTIFICATION (AuthController)
    // ------------------------------------
    case 'inscription':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->afficherInscription();
        break;

    case 'connexion':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->afficherConnexion();
        break;

    case 'traiter_inscription': // Quand le formulaire POST est envoyé
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->traiterInscription();
        break;

    case 'traiter_connexion':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->traiterConnexion();
        break;

    // Inscription / profile joueur
    case 'inscription_joueur':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        $controller = new JoueurController();
        $controller->afficherInscriptionJoueur();
        break;

    case 'traiter_inscription_joueur':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        $controller = new JoueurController();
        $controller->traiterInscriptionJoueur();
        break;

    case 'profile':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        $controller = new JoueurController();
        $controller->afficherProfile();
        break;

    case 'traiter_profile':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        $controller = new JoueurController();
        $controller->traiterProfile();
        break;

    // ------------------------------------
    // ZONE SPORTIVE (Equipe, Tournoi, Stats)
    // ------------------------------------
    case 'equipe':
        require_once __DIR__ . '/../controllers/EquipeController.php';
        $controller = new EquipeController();
        $controller->afficherEquipe();
        break;

    case 'tournoi':
        require_once __DIR__ . '/../controllers/TournoiController.php';
        $controller = new TournoiController();
        $controller->afficherTournoi();
        break;

    case 'statistiques':
        require_once __DIR__ . '/../controllers/StatsController.php';
        $controller = new StatsController();
        $controller->afficherStatistiques();
        break;

    // ------------------------------------
    // SÉCURITÉ : Page introuvable ou URL modifiée
    // ------------------------------------
    default:
        // Si l'action n'existe pas, on redirige vers l'accueil
        require_once __DIR__ . '/../controllers/HomeController.php';
        $controller = new HomeController();
        $controller->afficherAccueil();
        break;
}
