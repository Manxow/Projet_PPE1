<?php
// controllers/HomeController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/ModelJoueur.php';
require_once __DIR__ . '/../models/ModelTournoi.php';
require_once __DIR__ . '/../models/ModelMatch.php';

class HomeController
{
    public function afficherAccueil()
    {
        // On vérifie la connexion avec la bonne variable de session
        $estConnecte = isset($_SESSION['id_joueur']);
        $aUneEquipe = false;
        $idEquipe = null;

        $monTournoiEnCours = null;
        $prochainsTournois = ModelTournoi::getProchainsTournois(3);
        $prochainsMatchs = [];
        $derniersMatchs = [];

        // Si le joueur est connecté, on vérifie s'il a une équipe
        if ($estConnecte) {
            $id_joueur = $_SESSION['id_joueur'];
            $joueur = ModelJoueur::getJoueurById($id_joueur);

            if ($joueur && $joueur['id_equipe'] !== null) {
                $aUneEquipe = true;
                $idEquipe = (int)$joueur['id_equipe'];

                $monTournoiEnCours = ModelTournoi::getTournoiEnCoursEquipe($idEquipe);
                $prochainsMatchs = ModelMatch::getProchainsMatchsEquipe($idEquipe, 3);
                $derniersMatchs = ModelMatch::getDerniersMatchsEquipe($idEquipe, 3);

                foreach ($prochainsMatchs as &$match) {
                    $match['phase_affichage'] = self::formaterPhase($match['phase']);
                }
                unset($match);

                foreach ($derniersMatchs as &$match) {
                    $match['phase_affichage'] = self::formaterPhase($match['phase']);
                }
                unset($match);
            }
        }

        $typePage = 'onglet';
        $fichierVue = 'tabs/accueil.php';
        $action = 'accueil';

        require_once __DIR__ . '/../views/layout.php';
    }

    private static function formaterPhase($phase)
    {
        switch ($phase) {
            case 'poule':
                return 'Poule';
            case '1_4-finale':
            case 'quart':
                return 'QF';
            case '1_2-finale':
            case 'demi':
                return 'SM';
            case 'finale':
                return 'F';
            default:
                return strtoupper((string)$phase);
        }
    }
}
