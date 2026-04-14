<?php
// public/index.php
require_once __DIR__ . '/../models/ModelJoueur.php';
require_once __DIR__ . '/../models/ModelEquipe.php';

// Initialisation de la session si elle n'existe pas encore
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupération de l'action demandée (par défaut 'accueil')
$action = $_GET['action'] ?? 'accueil';

// =========================================================================
// LE ROUTEUR PRINCIPAL (Aiguilleur)
// =========================================================================
switch ($action) {

    // ---------------------------------------------------------------------
    // 1. ZONE PUBLIQUE (Visiteurs)
    // ---------------------------------------------------------------------
    case 'accueil':
        require_once __DIR__ . '/../controllers/HomeController.php';
        (new HomeController())->afficherAccueil();
        break;

    // ---------------------------------------------------------------------
    // 2. AUTHENTIFICATION (Connexion, Déconnexion, Traitements)
    // ---------------------------------------------------------------------
    case 'connexion':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->afficherConnexion();
        break;

    case 'traiter_connexion':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->traiterConnexion();
        break;

    case 'deconnexion':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->deconnexion();
        break;

    case 'inscription':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->afficherInscription();
        break;

    case 'traiter_inscription_joueur':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->traiterInscription();
        break;

    // ---------------------------------------------------------------------
    // 3. GESTION DU PROFIL JOUEUR
    // ---------------------------------------------------------------------
    case 'inscription_joueur':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        (new JoueurController())->afficherInscriptionJoueur();
        break;

    case 'profile':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        (new JoueurController())->afficherProfile();
        break;

    case 'traiter_profile':
        require_once __DIR__ . '/../controllers/JoueurController.php';
        (new JoueurController())->traiterProfile();
        break;

    // ---------------------------------------------------------------------
    // 4. GESTION DES ÉQUIPES (Côté Joueur / Capitaine)
    // ---------------------------------------------------------------------
    case 'equipe':
        require_once __DIR__ . '/../controllers/EquipeController.php';
        (new EquipeController())->afficherEquipe();
        break;

    case 'creer_equipe':
        require_once __DIR__ . '/../controllers/EquipeController.php';
        (new EquipeController())->traiterCreerEquipe();
        break;

    case 'rejoindre_equipe':
        require_once __DIR__ . '/../controllers/EquipeController.php';
        (new EquipeController())->traiterRejoindreEquipe();
        break;

    case 'quitter_equipe':
        require_once __DIR__ . '/../controllers/EquipeController.php';
        (new EquipeController())->traiterQuitterEquipe();
        break;

    // ---------------------------------------------------------------------
    // 5. ZONE SPORTIVE (Tournois & Statistiques côté Joueur)
    // ---------------------------------------------------------------------
    case 'tournoi':
        require_once __DIR__ . '/../controllers/TournoiController.php';
        (new TournoiController())->afficherTournois();
        break;

    case 'inscription_tournoi':
        require_once __DIR__ . '/../controllers/TournoiController.php';
        (new TournoiController())->traiterInscription();
        break;

    case 'statistiques':
        require_once __DIR__ . '/../controllers/StatsController.php';
        (new StatsController())->afficherStatistiques();
        break;

    // =====================================================================
    // 7. GESTION DES MATCHS
    // =====================================================================
    case 'saisir_resultat':
        require_once __DIR__ . '/../controllers/MatchController.php';
        (new MatchController())->afficherSaisieResultat();
        break;

    case 'traiter_saisir_resultat':
        require_once __DIR__ . '/../controllers/MatchController.php';
        (new MatchController())->traiterSaisieResultat();
        break;

    // =====================================================================
    // 8. ZONE ADMINISTRATION (Réservé aux Admins)
    // =====================================================================
    case 'admin_panel':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->afficherPanel();
        break;

    // Actions Admin : Équipes
    case 'accepter_equipe':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->accepterEquipe();
        break;

    case 'refuser_equipe':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->refuserEquipe();
        break;

    // Actions Admin : Tournois
    case 'admin_creer_tournoi':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->traiterCreerTournoi();
        break;

    case 'admin_supprimer_tournoi':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->traiterSupprimerTournoi();
        break;

    case 'admin_generer_poules':
        require_once __DIR__ . '/../controllers/PouleController.php';
        (new PouleController())->generer();
        break;

    case 'voir_poules':
        require_once __DIR__ . '/../controllers/PouleController.php';
        (new PouleController())->voirPoules();
        break;

    // --- Édition contextuelle ---

    //tournoi

    case 'traiter_editer_tournoi':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->traiterEditerTournoi();
        break;

    //équipe

    case 'traiter_editer_equipe':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->traiterEditerEquipe();
        break;

    // Dans le switch ($action) de index.php

    case 'admin_supprimer_equipe':
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->traiterSupprimerEquipe();
        break;


    // ---------------------------------------------------------------------
    // 7. SÉCURITÉ (Page introuvable ou URL bidouillée)
    // ---------------------------------------------------------------------
    default:
        // Redirection silencieuse vers l'accueil
        require_once __DIR__ . '/../controllers/HomeController.php';
        (new HomeController())->afficherAccueil();
        break;
}
