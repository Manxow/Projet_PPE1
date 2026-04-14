<?php

require_once __DIR__ . '/../config/database.php';

class ModelPoule
{ // On suppose que tu as une classe Model de base pour le PDO

    /**
     * Récupère les 16 équipes inscrites à un tournoi spécifique
     */
    public static function getInscritsPourTirage($idTournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT id_equipe FROM inscription_tournoi 
                WHERE id_tournoi = :id_t 
                ORDER BY date_inscription ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_t' => $idTournoi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assigne une lettre de poule (A, B, C, D) à une équipe pour un tournoi
     */
    public static function assignerEquipeAPoule($idTournoi, $idEquipe, $lettrePoule)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE inscription_tournoi 
                SET poule = :poule 
                WHERE id_tournoi = :id_t AND id_equipe = :id_e";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'poule' => $lettrePoule,
            'id_t'  => $idTournoi,
            'id_e'  => $idEquipe
        ]);
    }

    /**
     * Récupère toutes les équipes d'un tournoi, triées par poule
     */
    public static function getPoulesComplet($idTournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT i.poule, e.nom, e.id_equipe 
            FROM inscription_tournoi i
            JOIN equipe e ON i.id_equipe = e.id_equipe
            WHERE i.id_tournoi = :id_t AND i.poule IS NOT NULL
            ORDER BY i.poule ASC, e.nom ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_t' => $idTournoi]);

        // On organise les données par lettre pour faciliter la boucle dans la vue
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $poules = ['A' => [], 'B' => [], 'C' => [], 'D' => []];

        foreach ($resultats as $row) {
            $poules[$row['poule']][] = $row;
        }
        return $poules;
    }
}
