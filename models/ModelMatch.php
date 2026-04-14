<?php

require_once __DIR__ . '/../config/database.php';

class ModelMatch
{
    /**
     * Crée automatiquement tous les matchs pour une poule donnée
     * (tous les équipes jouent contre tous)
     */
    public static function genererMatchsPoule($id_tournoi, $poule)
    {
        $pdo = database::Connexion();

        // 1. Récupérer les 4 équipes de cette poule
        $sql = "SELECT id_equipe FROM inscription_tournoi 
                WHERE id_tournoi = :id_t AND poule = :p 
                ORDER BY date_inscription ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':p' => $poule]);
        $equipes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($equipes) < 2) {
            return false; // Pas assez d'équipes
        }

        // 2. Créer les matchs (combinaisons sans répétition : A vs B, A vs C, A vs D, B vs C, B vs D, C vs D)
        for ($i = 0; $i < count($equipes); $i++) {
            for ($j = $i + 1; $j < count($equipes); $j++) {
                $sql_insert = "INSERT INTO rencontre 
                              (id_tournoi, id_poule, id_equipe1, id_equipe2, phase, statut)
                              VALUES (:id_t, :poule, :eq1, :eq2, 'poule', 'à_jouer')";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':id_t' => $id_tournoi,
                    ':poule' => $poule,
                    ':eq1' => $equipes[$i],
                    ':eq2' => $equipes[$j]
                ]);
            }
        }

        return true;
    }

    /**
     * Récupère tous les matchs d'une poule d'un tournoi
     */
    public static function getMatchsPoule($id_tournoi, $poule)
    {
        $pdo = database::Connexion();
        $sql = "SELECT r.*, 
                e1.nom AS nom_equipe1, 
                e2.nom AS nom_equipe2
                FROM rencontre r
                JOIN equipe e1 ON r.id_equipe1 = e1.id_equipe
                JOIN equipe e2 ON r.id_equipe2 = e2.id_equipe
                WHERE r.id_tournoi = :id_t AND r.id_poule = :poule
                ORDER BY r.date_match ASC, r.id_rencontre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':poule' => $poule]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Saisit le résultat d'un match par le capitaine d'une équipe
     */
    public static function saisirResultat($id_rencontre, $id_equipe_saisie, $buts_eq1, $buts_eq2)
    {
        $pdo = database::Connexion();

        // Récupérer le match
        $sql = "SELECT id_equipe1, id_equipe2, buts_equipe1_saisie1, buts_equipe2_saisie1,
                       buts_equipe1_saisie2, buts_equipe2_saisie2, statut
                FROM rencontre WHERE id_rencontre = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_rencontre]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) {
            return ['succes' => false, 'message' => 'Match introuvable'];
        }

        // Vérifier que c'est bien un capitaine d'une des deux équipes
        if ($id_equipe_saisie != $match['id_equipe1'] && $id_equipe_saisie != $match['id_equipe2']) {
            return ['succes' => false, 'message' => 'Vous ne faites pas partie de ce match'];
        }

        // Saisir la première saisie ou la deuxième ?
        if ($match['buts_equipe1_saisie1'] === null) {
            // Première saisie dans le système
            $sql_update = "UPDATE rencontre SET buts_equipe1_saisie1 = :eq1, buts_equipe2_saisie1 = :eq2
                          WHERE id_rencontre = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $result = $stmt_update->execute([':eq1' => $buts_eq1, ':eq2' => $buts_eq2, ':id' => $id_rencontre]);

            if ($result) {
                return ['succes' => true, 'message' => 'Résultat saisi en attente de confirmation'];
            }
        } else {
            // Deuxième saisie (vérifier si elle correspond)
            if ($match['buts_equipe1_saisie1'] == $buts_eq1 && $match['buts_equipe2_saisie1'] == $buts_eq2) {
                // Les résultats correspondent !
                $sql_update = "UPDATE rencontre SET 
                              buts_equipe1_final = :eq1, 
                              buts_equipe2_final = :eq2,
                              statut = 'terminé'
                              WHERE id_rencontre = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $result = $stmt_update->execute([':eq1' => $buts_eq1, ':eq2' => $buts_eq2, ':id' => $id_rencontre]);

                if ($result) {
                    return ['succes' => true, 'message' => 'Match validé !'];
                }
            } else {
                // Les résultats ne correspondent pas
                $sql_update = "UPDATE rencontre SET 
                              buts_equipe1_saisie2 = :eq1, 
                              buts_equipe2_saisie2 = :eq2,
                              statut = 'erreur'
                              WHERE id_rencontre = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $result = $stmt_update->execute([':eq1' => $buts_eq1, ':eq2' => $buts_eq2, ':id' => $id_rencontre]);

                if ($result) {
                    return ['succes' => false, 'message' => 'Erreur : les résultats ne correspondent pas ! Contactez l\'administrateur.'];
                }
            }
        }

        return ['succes' => false, 'message' => 'Erreur lors de la mise à jour'];
    }

    /**
     * Récupère le statut d'un match et ses résultats
     */
    public static function getMatch($id_rencontre)
    {
        $pdo = database::Connexion();
        $sql = "SELECT r.*, 
                e1.nom AS nom_equipe1, 
                e2.nom AS nom_equipe2
                FROM rencontre r
                JOIN equipe e1 ON r.id_equipe1 = e1.id_equipe
                JOIN equipe e2 ON r.id_equipe2 = e2.id_equipe
                WHERE r.id_rencontre = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_rencontre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les matchs d'un tournoi
     */
    public static function getMatchsTournoi($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT r.*, 
                e1.nom AS nom_equipe1, 
                e2.nom AS nom_equipe2
                FROM rencontre r
                JOIN equipe e1 ON r.id_equipe1 = e1.id_equipe
                JOIN equipe e2 ON r.id_equipe2 = e2.id_equipe
                WHERE r.id_tournoi = :id_t
                ORDER BY r.id_poule ASC, r.phase ASC, r.date_match ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Les prochains matchs à jouer pour une équipe.
     */
    public static function getProchainsMatchsEquipe($id_equipe, $limit = 3)
    {
        $pdo = database::Connexion();
        $limit = max(1, (int)$limit);

        $sql = "SELECT r.id_rencontre, r.id_tournoi, r.phase, r.date_match, r.statut,
                       e1.nom AS nom_equipe1,
                       e2.nom AS nom_equipe2,
                       t.nom AS nom_tournoi
                FROM rencontre r
                JOIN equipe e1 ON e1.id_equipe = r.id_equipe1
                JOIN equipe e2 ON e2.id_equipe = r.id_equipe2
                JOIN tournoi t ON t.id_tournoi = r.id_tournoi
                WHERE (r.id_equipe1 = :id_e OR r.id_equipe2 = :id_e)
                  AND r.statut = 'à_jouer'
                ORDER BY
                    CASE WHEN r.date_match IS NULL THEN 1 ELSE 0 END,
                    r.date_match ASC,
                    r.id_rencontre ASC
                LIMIT $limit";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_e' => (int)$id_equipe]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Les derniers matchs validés pour une équipe.
     */
    public static function getDerniersMatchsEquipe($id_equipe, $limit = 3)
    {
        $pdo = database::Connexion();
        $limit = max(1, (int)$limit);

        $sql = "SELECT r.id_rencontre, r.id_tournoi, r.phase, r.date_match, r.statut,
                       r.buts_equipe1_final, r.buts_equipe2_final,
                       e1.nom AS nom_equipe1,
                       e2.nom AS nom_equipe2,
                       t.nom AS nom_tournoi
                FROM rencontre r
                JOIN equipe e1 ON e1.id_equipe = r.id_equipe1
                JOIN equipe e2 ON e2.id_equipe = r.id_equipe2
                JOIN tournoi t ON t.id_tournoi = r.id_tournoi
                WHERE (r.id_equipe1 = :id_e OR r.id_equipe2 = :id_e)
                  AND r.statut = 'terminé'
                  AND r.buts_equipe1_final IS NOT NULL
                  AND r.buts_equipe2_final IS NOT NULL
                ORDER BY
                    CASE WHEN r.date_match IS NULL THEN 1 ELSE 0 END,
                    r.date_match DESC,
                    r.id_rencontre DESC
                LIMIT $limit";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_e' => (int)$id_equipe]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule le classement d'une poule (3 points victoire, 1 nul, 0 défaite)
     */
    public static function getClassementPoule($id_tournoi, $poule)
    {
        $pdo = database::Connexion();

        $sql = "SELECT
                    i.id_equipe,
                    e.nom,
                    SUM(
                        CASE
                            WHEN r.id_rencontre IS NULL THEN 0
                            WHEN r.id_equipe1 = i.id_equipe AND r.buts_equipe1_final > r.buts_equipe2_final THEN 3
                            WHEN r.id_equipe2 = i.id_equipe AND r.buts_equipe2_final > r.buts_equipe1_final THEN 3
                            WHEN r.buts_equipe1_final = r.buts_equipe2_final THEN 1
                            ELSE 0
                        END
                    ) AS points,
                    SUM(
                        CASE
                            WHEN r.id_rencontre IS NULL THEN 0
                            WHEN r.id_equipe1 = i.id_equipe THEN 1
                            WHEN r.id_equipe2 = i.id_equipe THEN 1
                            ELSE 0
                        END
                    ) AS matchs_joues,
                    SUM(
                        CASE
                            WHEN r.id_rencontre IS NULL THEN 0
                            WHEN r.id_equipe1 = i.id_equipe THEN r.buts_equipe1_final
                            WHEN r.id_equipe2 = i.id_equipe THEN r.buts_equipe2_final
                            ELSE 0
                        END
                    ) AS buts_pour,
                    SUM(
                        CASE
                            WHEN r.id_rencontre IS NULL THEN 0
                            WHEN r.id_equipe1 = i.id_equipe THEN r.buts_equipe2_final
                            WHEN r.id_equipe2 = i.id_equipe THEN r.buts_equipe1_final
                            ELSE 0
                        END
                    ) AS buts_contre
                FROM inscription_tournoi i
                JOIN equipe e ON e.id_equipe = i.id_equipe
                LEFT JOIN rencontre r
                    ON r.id_tournoi = i.id_tournoi
                    AND r.id_poule = i.poule
                    AND r.phase = 'poule'
                    AND r.statut = 'terminé'
                    AND (r.id_equipe1 = i.id_equipe OR r.id_equipe2 = i.id_equipe)
                WHERE i.id_tournoi = :id_t AND i.poule = :poule
                GROUP BY i.id_equipe, e.nom
                ORDER BY points DESC,
                         (buts_pour - buts_contre) DESC,
                         buts_pour DESC,
                         e.nom ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':poule' => $poule]);
        $classement = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Règle spéciale demandée : en fin de poule, pour une égalité à 2 équipes,
        // on départage avec la confrontation directe.
        if (self::pouleTerminee($id_tournoi, $poule)) {
            $classement = self::appliquerDepartageConfrontationDirecte($id_tournoi, $poule, $classement);
        }

        foreach ($classement as $index => &$ligne) {
            $ligne['rang'] = $index + 1;
            $ligne['difference_buts'] = (int)$ligne['buts_pour'] - (int)$ligne['buts_contre'];
            $ligne['qualifie'] = ($index < 2);
        }
        unset($ligne);

        return $classement;
    }

    /**
     * Récupère les classements de toutes les poules d'un tournoi
     */
    public static function getClassementsTournoi($id_tournoi)
    {
        $poules = ['A', 'B', 'C', 'D'];
        $resultats = [];

        foreach ($poules as $poule) {
            $resultats[$poule] = self::getClassementPoule($id_tournoi, $poule);
        }

        return $resultats;
    }

    /**
     * Vérifie si tous les matchs d'une poule sont terminés.
     */
    public static function pouleTerminee($id_tournoi, $poule)
    {
        $pdo = database::Connexion();
        $sql = "SELECT
                    SUM(CASE WHEN statut = 'terminé' THEN 1 ELSE 0 END) AS nb_termines,
                    COUNT(*) AS nb_total
                FROM rencontre
                WHERE id_tournoi = :id_t
                  AND phase = 'poule'
                  AND id_poule = :poule";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':poule' => $poule]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || (int)$row['nb_total'] === 0) {
            return false;
        }

        return (int)$row['nb_termines'] === (int)$row['nb_total'];
    }

    /**
     * Départage les égalités de points à 2 équipes via confrontation directe.
     * Si la confrontation directe est nulle, on conserve l'ordre existant (règle future).
     */
    private static function appliquerDepartageConfrontationDirecte($id_tournoi, $poule, array $classement)
    {
        $groupes = [];
        foreach ($classement as $index => $ligne) {
            $points = (int)$ligne['points'];
            if (!isset($groupes[$points])) {
                $groupes[$points] = [];
            }
            $groupes[$points][] = $index;
        }

        foreach ($groupes as $indexes) {
            if (count($indexes) !== 2) {
                continue;
            }

            $i1 = $indexes[0];
            $i2 = $indexes[1];
            $eq1 = (int)$classement[$i1]['id_equipe'];
            $eq2 = (int)$classement[$i2]['id_equipe'];

            $gagnant = self::getGagnantConfrontationDirecte($id_tournoi, $poule, $eq1, $eq2);

            if ($gagnant === null) {
                // Tirage au sort : égalité de points ET confrontation directe nulle
                if (array_rand([0, 1]) === 1) {
                    $tmp = $classement[$i1];
                    $classement[$i1] = $classement[$i2];
                    $classement[$i2] = $tmp;
                }
                continue;
            }

            // Le gagnant doit être classé devant le perdant.
            if ($gagnant === $eq2) {
                $tmp = $classement[$i1];
                $classement[$i1] = $classement[$i2];
                $classement[$i2] = $tmp;
            }
        }

        return $classement;
    }

    /**
     * Retourne l'id de l'équipe gagnante de la confrontation directe, ou null si nul/absent.
     */
    private static function getGagnantConfrontationDirecte($id_tournoi, $poule, $id_equipe_a, $id_equipe_b)
    {
        $pdo = database::Connexion();
        $sql = "SELECT id_equipe1, id_equipe2, buts_equipe1_final, buts_equipe2_final
                FROM rencontre
                WHERE id_tournoi = :id_t
                  AND id_poule = :poule
                  AND phase = 'poule'
                  AND statut = 'terminé'
                  AND ((id_equipe1 = :eq_a AND id_equipe2 = :eq_b)
                    OR (id_equipe1 = :eq_b AND id_equipe2 = :eq_a))
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_t' => $id_tournoi,
            ':poule' => $poule,
            ':eq_a' => $id_equipe_a,
            ':eq_b' => $id_equipe_b,
        ]);

        $match = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$match) {
            return null;
        }

        $b1 = (int)$match['buts_equipe1_final'];
        $b2 = (int)$match['buts_equipe2_final'];

        if ($b1 === $b2) {
            return null;
        }

        return ($b1 > $b2) ? (int)$match['id_equipe1'] : (int)$match['id_equipe2'];
    }

    /**
     * Vérifie si tous les matchs de poules sont terminés
     */
    public static function phasePouleTerminee($id_tournoi)
    {
        $pdo = database::Connexion();

        $sql = "SELECT
                    SUM(CASE WHEN statut = 'terminé' THEN 1 ELSE 0 END) AS nb_termines,
                    COUNT(*) AS nb_total
                FROM rencontre
                WHERE id_tournoi = :id_t
                  AND phase = 'poule'
                  AND id_poule IS NOT NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || (int)$row['nb_total'] === 0) {
            return false;
        }

        return (int)$row['nb_termines'] === (int)$row['nb_total'];
    }

    /**
     * Vérifie si des phases finales existent déjà
     */
    public static function phasesFinalesDejaGenerees($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT COUNT(*) FROM rencontre WHERE id_tournoi = :id_t AND phase <> 'poule'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Génère automatiquement les quarts si la phase de poules est terminée
     */
    public static function genererPhasesFinalSiPret($id_tournoi)
    {
        if (!self::phasePouleTerminee($id_tournoi)) {
            return ['succes' => false, 'message' => null];
        }

        if (self::phasesFinalesDejaGenerees($id_tournoi)) {
            return ['succes' => false, 'message' => null];
        }

        $ok = self::genererPhasesFinal($id_tournoi);
        if ($ok) {
            return ['succes' => true, 'message' => 'Phase de poules terminée : les phases finales ont été générées automatiquement.'];
        }

        return ['succes' => false, 'message' => null];
    }

    /**
     * Génère le tirage au sort des quarts de finale.
     * Les premiers ET les deuxièmes de chaque poule sont mélangés aléatoirement,
     * avec la contrainte qu'un 1er ne joue jamais contre le 2ème de sa propre poule.
     */
    public static function genererPhasesFinal($id_tournoi)
    {
        $pdo = database::Connexion();

        $classements = self::getClassementsTournoi($id_tournoi);

        foreach (['A', 'B', 'C', 'D'] as $poule) {
            if (count($classements[$poule]) < 2) {
                return false;
            }
        }

        // Construire les tableaux de premiers et deuxièmes avec leur poule d'origine
        $premiers  = [];
        $deuxiemes = [];
        foreach (['A', 'B', 'C', 'D'] as $poule) {
            $premiers[]  = ['id' => (int)$classements[$poule][0]['id_equipe'], 'poule' => $poule];
            $deuxiemes[] = ['id' => (int)$classements[$poule][1]['id_equipe'], 'poule' => $poule];
        }

        // Mélanger les premiers
        shuffle($premiers);

        // Mélanger les deuxièmes jusqu'à obtenir un appariement valide
        // (aucun 1er ne joue contre le 2ème de sa propre poule)
        $maxEssais = 100;
        $essai     = 0;
        do {
            shuffle($deuxiemes);
            $valide = true;
            for ($i = 0; $i < 4; $i++) {
                if ($premiers[$i]['poule'] === $deuxiemes[$i]['poule']) {
                    $valide = false;
                    break;
                }
            }
            $essai++;
        } while (!$valide && $essai < $maxEssais);

        if (!$valide) {
            return false;
        }

        foreach ($premiers as $i => $premier) {
            $sql = "INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut)
                    VALUES (:id_t, :eq1, :eq2, '1_4-finale', 'à_jouer')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_t' => $id_tournoi, ':eq1' => $premier['id'], ':eq2' => $deuxiemes[$i]['id']]);
        }

        return true;
    }

    /**
     * Retourne l'id de l'équipe gagnante d'un match terminé, ou null si nul/non terminé.
     */
    private static function getVainqueurMatch(array $match): ?int
    {
        if ($match['statut'] !== 'terminé') return null;
        $b1 = (int)$match['buts_equipe1_final'];
        $b2 = (int)$match['buts_equipe2_final'];
        if ($b1 > $b2) return (int)$match['id_equipe1'];
        if ($b2 > $b1) return (int)$match['id_equipe2'];
        return null;
    }

    /**
     * Génère les demi-finales si les quarts correspondants sont terminés.
     * QF1+QF2 → SF1 ; QF3+QF4 → SF2 (traités indépendamment).
     */
    public static function genererDemiFinalesSiPret($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT r.*, e1.nom AS nom_equipe1, e2.nom AS nom_equipe2
                FROM rencontre r
                JOIN equipe e1 ON r.id_equipe1 = e1.id_equipe
                JOIN equipe e2 ON r.id_equipe2 = e2.id_equipe
                WHERE r.id_tournoi = :id_t AND r.phase = '1_4-finale'
                ORDER BY r.id_rencontre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        $qfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($qfs) !== 4) return ['succes' => false, 'message' => null];

        $cree     = false;
        $messages = [];

        // Paire 1 : QF[0] + QF[1] → Demi-finale 1
        if ($qfs[0]['statut'] === 'terminé' && $qfs[1]['statut'] === 'terminé') {
            $v1 = self::getVainqueurMatch($qfs[0]);
            $v2 = self::getVainqueurMatch($qfs[1]);
            if ($v1 && $v2) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM rencontre WHERE id_tournoi = :id_t AND phase = '1_2-finale' AND (id_equipe1 = :v OR id_equipe2 = :v)");
                $check->execute([':id_t' => $id_tournoi, ':v' => $v1]);
                if ((int)$check->fetchColumn() === 0) {
                    $ins = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut) VALUES (:id_t, :eq1, :eq2, '1_2-finale', 'à_jouer')");
                    $ins->execute([':id_t' => $id_tournoi, ':eq1' => $v1, ':eq2' => $v2]);
                    $cree      = true;
                    $messages[] = 'Demi-finale 1 générée.';
                }
            }
        }

        // Paire 2 : QF[2] + QF[3] → Demi-finale 2
        if ($qfs[2]['statut'] === 'terminé' && $qfs[3]['statut'] === 'terminé') {
            $v3 = self::getVainqueurMatch($qfs[2]);
            $v4 = self::getVainqueurMatch($qfs[3]);
            if ($v3 && $v4) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM rencontre WHERE id_tournoi = :id_t AND phase = '1_2-finale' AND (id_equipe1 = :v OR id_equipe2 = :v)");
                $check->execute([':id_t' => $id_tournoi, ':v' => $v3]);
                if ((int)$check->fetchColumn() === 0) {
                    $ins = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut) VALUES (:id_t, :eq1, :eq2, '1_2-finale', 'à_jouer')");
                    $ins->execute([':id_t' => $id_tournoi, ':eq1' => $v3, ':eq2' => $v4]);
                    $cree      = true;
                    $messages[] = 'Demi-finale 2 générée.';
                }
            }
        }

        if ($cree) {
            return ['succes' => true, 'message' => implode(' ', $messages)];
        }
        return ['succes' => false, 'message' => null];
    }

    /**
     * Génère la finale quand les deux demi-finales sont terminées.
     */
    public static function genererFinaleSiPret($id_tournoi)
    {
        $pdo = database::Connexion();
        $sql = "SELECT r.*, e1.nom AS nom_equipe1, e2.nom AS nom_equipe2
                FROM rencontre r
                JOIN equipe e1 ON r.id_equipe1 = e1.id_equipe
                JOIN equipe e2 ON r.id_equipe2 = e2.id_equipe
                WHERE r.id_tournoi = :id_t AND r.phase = '1_2-finale'
                ORDER BY r.id_rencontre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        $sfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($sfs) !== 2) return ['succes' => false, 'message' => null];
        if ($sfs[0]['statut'] !== 'terminé' || $sfs[1]['statut'] !== 'terminé') return ['succes' => false, 'message' => null];

        $v1 = self::getVainqueurMatch($sfs[0]);
        $v2 = self::getVainqueurMatch($sfs[1]);
        if (!$v1 || !$v2) return ['succes' => false, 'message' => null];

        $check = $pdo->prepare("SELECT COUNT(*) FROM rencontre WHERE id_tournoi = :id_t AND phase = 'finale'");
        $check->execute([':id_t' => $id_tournoi]);
        if ((int)$check->fetchColumn() > 0) return ['succes' => false, 'message' => null];

        $ins = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut) VALUES (:id_t, :eq1, :eq2, 'finale', 'à_jouer')");
        $ins->execute([':id_t' => $id_tournoi, ':eq1' => $v1, ':eq2' => $v2]);
        return ['succes' => true, 'message' => '🏆 La finale a été générée !'];
    }
}
