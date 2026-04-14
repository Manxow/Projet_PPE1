<?php
require_once __DIR__ . '/../config/database.php';

class ModelTournoi
{

    // Récupérer les tournois à venir (ceux qui ne sont pas 'termine')
    public static function getTournoisDisponibles()
    {
        $pdo = database::Connexion();
        $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM inscription_tournoi WHERE id_tournoi = t.id_tournoi) as nb_inscrits
                FROM tournoi t 
                WHERE t.statut != 'termine' 
                ORDER BY t.date_debut ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // CREATION TOURNOI (ADMIN) 

    public static function creerTournoi($nom, $debut, $fin)
    {
        $pdo = database::Connexion();
        $sql = "INSERT INTO tournoi (nom, date_debut, date_fin, statut) 
                VALUES (:nom, :debut, :fin, 'ouvert')";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':nom'   => $nom,
            ':debut' => $debut,
            ':fin'   => $fin
        ]);
    }

    // SUPPRESSION TOURNOI (ADMIN)

    public static function supprimerTournoi($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "DELETE FROM tournoi WHERE id_tournoi = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id_tournoi]);
    }

    // Vérifier si une équipe spécifique est déjà inscrite à un tournoi précis
    public static function estDejaInscrit($id_equipe, $id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT COUNT(*) FROM inscription_tournoi WHERE id_equipe = :idE AND id_tournoi = :idT";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idE' => $id_equipe, ':idT' => $id_tournoi]);
        return $stmt->fetchColumn() > 0;
    }

    // Inscrire l'équipe et verrouiller le tournoi si on atteint 16
    public static function inscrireEquipe($id_equipe, $id_tournoi)
    {
        $pdo = database::Connexion();

        // 1. On compte le nombre actuel d'inscrits
        $sqlCount = "SELECT COUNT(*) FROM inscription_tournoi WHERE id_tournoi = :idT";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute([':idT' => $id_tournoi]);
        $nbInscrits = $stmtCount->fetchColumn();

        // 2. Si on est déjà à 16 ou plus, on bloque direct
        if ($nbInscrits >= 16) {
            return false; // Tournoi complet
        }

        // 3. On insère la nouvelle équipe
        $sql = "INSERT INTO inscription_tournoi (id_equipe, id_tournoi) VALUES (:idE, :idT)";
        $stmt = $pdo->prepare($sql);
        $succes = $stmt->execute([':idE' => $id_equipe, ':idT' => $id_tournoi]);

        // 4. LA LOGIQUE MÉTIER : Si l'inscription a marché, on vérifie si c'était la 16ème équipe !
        if ($succes) {
            // Si on avait 15 inscrits avant, et que l'insertion a marché, on est donc à 16.
            if ($nbInscrits + 1 == 16) {
                // On verrouille le tournoi en changeant son statut
                $sqlUpdate = "UPDATE tournoi SET statut = 'complet' WHERE id_tournoi = :idT";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([':idT' => $id_tournoi]);
            }
            return true;
        }

        return false;
    }


    // Compter le nombre de tournois qui attendent leur tirage au sort
    public static function getNbTournoisComplets()
    {
        $pdo = database::Connexion();
        $sql = "SELECT COUNT(*) FROM tournoi WHERE statut = 'complet'";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    }
    // Récupérer un tournoi spécifique par son ID
    public static function getTournoiById($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT * FROM tournoi WHERE id_tournoi = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_tournoi]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour les infos d'un tournoi
    public static function modifierTournoi($id_tournoi, $nom, $debut, $fin)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE tournoi SET nom = :nom, date_debut = :debut, date_fin = :fin WHERE id_tournoi = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':nom'   => $nom,
            ':debut' => $debut,
            ':fin'   => $fin,
            ':id'    => $id_tournoi
        ]);
    }

    //Création de poule
    public static function enregistrerPoule($idTournoi, $idEquipe, $nomPoule)
    {
        $pdo = database::Connexion();
        // On met à jour l'inscription de l'équipe pour lui donner une poule
        $sql = "UPDATE inscription_tournoi 
            SET poule = :poule 
            WHERE id_tournoi = :id_t AND id_equipe = :id_e";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'poule' => $nomPoule,
            'id_t'  => $idTournoi,
            'id_e'  => $idEquipe
        ]);
    }

    // Récupérer les 16 IDs des équipes inscrites
    public static function getEquipesInscrites($idTournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT id_equipe FROM inscription_tournoi WHERE id_tournoi = :id ORDER BY date_inscription ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idTournoi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Assigner une lettre de poule à une équipe
    public static function majPouleEquipe($idTournoi, $idEquipe, $lettrePoule)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE inscription_tournoi SET poule = :poule 
            WHERE id_tournoi = :id_t AND id_equipe = :id_e";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'poule' => $lettrePoule,
            'id_t'  => $idTournoi,
            'id_e'  => $idEquipe
        ]);
    }

    public static function updateStatut($idTournoi, $nouveauStatut)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE tournoi SET statut = :statut WHERE id_tournoi = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'statut' => $nouveauStatut,
            'id'     => $idTournoi
        ]);
    }

    /**
     * Les prochains tournois (hors tournois déjà en cours/terminés).
     */
    public static function getProchainsTournois($limit = 3)
    {
        $pdo = database::Connexion();
        $limit = max(1, (int)$limit);

        $sql = "SELECT t.*,
                       (SELECT COUNT(*) FROM inscription_tournoi it WHERE it.id_tournoi = t.id_tournoi) AS nb_inscrits
                FROM tournoi t
                WHERE t.statut IN ('ouvert', 'complet')
                ORDER BY t.date_debut ASC, t.id_tournoi ASC
                LIMIT $limit";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Le tournoi en cours du joueur (via son équipe), s'il existe.
     */
    public static function getTournoiEnCoursEquipe($id_equipe)
    {
        $pdo = database::Connexion();
        $sql = "SELECT t.*,
                       (SELECT COUNT(*) FROM inscription_tournoi it WHERE it.id_tournoi = t.id_tournoi) AS nb_inscrits
                FROM tournoi t
                JOIN inscription_tournoi it ON it.id_tournoi = t.id_tournoi
                WHERE it.id_equipe = :id_e
                  AND t.statut = 'en_cours'
                ORDER BY t.date_debut ASC, t.id_tournoi ASC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_e' => (int)$id_equipe]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Récupérer les tournois terminés (statut = 'termine')
    public static function getTournoisTermines()
    {
        $pdo = database::Connexion();
        $sql = "SELECT t.*,
                (SELECT COUNT(*) FROM inscription_tournoi WHERE id_tournoi = t.id_tournoi) as nb_inscrits
                FROM tournoi t
                WHERE t.statut = 'termine'
                ORDER BY t.date_fin DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Equipes participantes d'un tournoi (ordre d'inscription).
     */
    public static function getParticipantsTournoi($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT e.id_equipe, e.nom, it.date_inscription
                FROM inscription_tournoi it
                JOIN equipe e ON e.id_equipe = it.id_equipe
                WHERE it.id_tournoi = :id_t
                ORDER BY it.date_inscription ASC, e.nom ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => (int)$id_tournoi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
