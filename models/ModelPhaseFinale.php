<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ModelMatch.php';

class ModelPhaseFinale
{
    public static function creerTableSiNecessaire()
    {
        $pdo = database::Connexion();
        $sql = "CREATE TABLE IF NOT EXISTS phase_finale (
                    id_phase_finale INT AUTO_INCREMENT PRIMARY KEY,
                    id_tournoi INT NOT NULL,
                    id_match INT NOT NULL,
                    phase ENUM('quart', 'demi', 'finale') NOT NULL,
                    position_phase TINYINT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_phase_finale_match FOREIGN KEY (id_match) REFERENCES rencontre(id_rencontre) ON DELETE CASCADE,
                    CONSTRAINT uq_phase_finale_unique_match UNIQUE (id_match),
                    CONSTRAINT uq_phase_finale_phase_position UNIQUE (id_tournoi, phase, position_phase)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);

        // Schéma unique impose pour garder un code clair et lisible.
        self::verifierSchemaConforme($pdo);
    }

    private static function verifierSchemaConforme(PDO $pdo)
    {
        $rows = $pdo->query("SHOW COLUMNS FROM phase_finale")->fetchAll(PDO::FETCH_ASSOC);
        $colonnes = array_map(static function ($row) {
            return $row['Field'];
        }, $rows);

        $attendues = ['id_phase_finale', 'id_tournoi', 'id_match', 'phase', 'position_phase', 'created_at'];
        foreach ($attendues as $colonne) {
            if (!in_array($colonne, $colonnes, true)) {
                throw new RuntimeException(
                    "Le schéma de phase_finale n'est pas conforme. Exécute d'abord config/sql/phase_finale.sql (DROP + CREATE) puis recharge la page."
                );
            }
        }
    }

    public static function getPhasesFinalesTournoi($id_tournoi)
    {
        self::creerTableSiNecessaire();

        $pdo = database::Connexion();
        $sql = "SELECT pf.phase, pf.position_phase,
                       r.id_rencontre,
                       r.id_equipe1, r.id_equipe2,
                       r.buts_equipe1_final, r.buts_equipe2_final,
                       r.date_match, r.statut,
                       e1.nom AS nom_equipe1,
                       e2.nom AS nom_equipe2
                FROM phase_finale pf
                JOIN rencontre r ON r.id_rencontre = pf.id_match
                JOIN equipe e1 ON e1.id_equipe = r.id_equipe1
                JOIN equipe e2 ON e2.id_equipe = r.id_equipe2
                WHERE pf.id_tournoi = :id_t
                ORDER BY
                    CASE pf.phase
                        WHEN 'quart' THEN 1
                        WHEN 'demi' THEN 2
                        WHEN 'finale' THEN 3
                    END,
                    pf.position_phase ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existePourTournoi($id_tournoi)
    {
        self::creerTableSiNecessaire();

        $pdo = database::Connexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM phase_finale WHERE id_tournoi = :id_t");
        $stmt->execute([':id_t' => $id_tournoi]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function synchroniserTournoi($id_tournoi)
    {
        self::creerTableSiNecessaire();

        if (!ModelMatch::phasePouleTerminee($id_tournoi)) {
            return ['succes' => false, 'message' => null];
        }

        // Migration douce : si des matchs de phases finales existent déjà dans rencontre,
        // on les réindexe dans phase_finale plutôt que de recréer un tableau en doublon.
        self::importerDepuisRencontreSiNecessaire($id_tournoi);

        $messages = [];

        if (!self::existePourTournoi($id_tournoi)) {
            if (!self::genererQuarts($id_tournoi)) {
                return ['succes' => false, 'message' => null];
            }
            $messages[] = 'Phase de poules terminée : quarts de finale générés.';
        }

        $demis = self::genererDemiSiPret($id_tournoi);
        if ($demis['succes'] && !empty($demis['message'])) {
            $messages[] = $demis['message'];
        }

        $finale = self::genererFinaleSiPret($id_tournoi);
        if ($finale['succes'] && !empty($finale['message'])) {
            $messages[] = $finale['message'];
        }

        if (empty($messages)) {
            return ['succes' => false, 'message' => null];
        }

        return ['succes' => true, 'message' => implode(' ', $messages)];
    }

    private static function importerDepuisRencontreSiNecessaire($id_tournoi)
    {
        if (self::existePourTournoi($id_tournoi)) {
            return;
        }

        $pdo = database::Connexion();

        $sql = "SELECT id_rencontre, phase
                FROM rencontre
                WHERE id_tournoi = :id_t
                  AND phase IN ('1_4-finale', '1_2-finale', 'finale')
                ORDER BY
                    CASE phase
                        WHEN '1_4-finale' THEN 1
                        WHEN '1_2-finale' THEN 2
                        WHEN 'finale' THEN 3
                    END,
                    id_rencontre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return;
        }

        $map = [
            '1_4-finale' => 'quart',
            '1_2-finale' => 'demi',
            'finale' => 'finale',
        ];

        $positions = ['quart' => 0, 'demi' => 0, 'finale' => 0];

        foreach ($rows as $row) {
            $phase = $map[$row['phase']];
            $positions[$phase]++;

            $ins = $pdo->prepare("INSERT IGNORE INTO phase_finale (id_tournoi, id_match, phase, position_phase)
                                  VALUES (:id_t, :id_m, :phase, :pos)");
            $ins->execute([
                ':id_t' => $id_tournoi,
                ':id_m' => (int)$row['id_rencontre'],
                ':phase' => $phase,
                ':pos' => $positions[$phase],
            ]);
        }
    }

    /**
     * Calcule le classement final d'un tournoi terminé.
     * Retourne : vainqueur, finaliste, demi-finalistes (2), quart-finalistes (4), éliminés phase de poule (8).
     */
    public static function getClassementFinal($id_tournoi)
    {
        $pdo = database::Connexion();

        $sql = "SELECT pf.phase, r.id_rencontre,
                       r.id_equipe1, r.id_equipe2,
                       r.buts_equipe1_final, r.buts_equipe2_final, r.statut,
                       e1.nom AS nom_equipe1, e2.nom AS nom_equipe2
                FROM phase_finale pf
                JOIN rencontre r ON r.id_rencontre = pf.id_match
                JOIN equipe e1 ON e1.id_equipe = r.id_equipe1
                JOIN equipe e2 ON e2.id_equipe = r.id_equipe2
                WHERE pf.id_tournoi = :id_t
                ORDER BY
                    CASE pf.phase WHEN 'quart' THEN 1 WHEN 'demi' THEN 2 WHEN 'finale' THEN 3 END,
                    pf.position_phase ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => (int)$id_tournoi]);
        $phases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $quarts  = [];
        $demis   = [];
        $finale  = null;

        foreach ($phases as $m) {
            if ($m['phase'] === 'quart')       $quarts[] = $m;
            elseif ($m['phase'] === 'demi')    $demis[]  = $m;
            elseif ($m['phase'] === 'finale')  $finale   = $m;
        }

        $vainqueur        = null;
        $finaliste        = null;
        $demi_finalistes  = [];
        $quart_finalistes = [];

        // Finale : gagnant = vainqueur, perdant = finaliste
        if ($finale && $finale['statut'] === 'terminé') {
            if ((int)$finale['buts_equipe1_final'] > (int)$finale['buts_equipe2_final']) {
                $vainqueur = ['id_equipe' => $finale['id_equipe1'], 'nom' => $finale['nom_equipe1']];
                $finaliste = ['id_equipe' => $finale['id_equipe2'], 'nom' => $finale['nom_equipe2']];
            } else {
                $vainqueur = ['id_equipe' => $finale['id_equipe2'], 'nom' => $finale['nom_equipe2']];
                $finaliste = ['id_equipe' => $finale['id_equipe1'], 'nom' => $finale['nom_equipe1']];
            }
        }

        // Demi-finales : les 2 perdants sont 3e ex-æquo
        foreach ($demis as $m) {
            if ($m['statut'] === 'terminé') {
                if ((int)$m['buts_equipe1_final'] > (int)$m['buts_equipe2_final']) {
                    $demi_finalistes[] = ['id_equipe' => $m['id_equipe2'], 'nom' => $m['nom_equipe2']];
                } else {
                    $demi_finalistes[] = ['id_equipe' => $m['id_equipe1'], 'nom' => $m['nom_equipe1']];
                }
            }
        }

        // Quarts : les 4 perdants sont 5e ex-æquo
        $ids_quarts_participants = [];
        foreach ($quarts as $m) {
            $ids_quarts_participants[] = (int)$m['id_equipe1'];
            $ids_quarts_participants[] = (int)$m['id_equipe2'];
            if ($m['statut'] === 'terminé') {
                if ((int)$m['buts_equipe1_final'] > (int)$m['buts_equipe2_final']) {
                    $quart_finalistes[] = ['id_equipe' => $m['id_equipe2'], 'nom' => $m['nom_equipe2']];
                } else {
                    $quart_finalistes[] = ['id_equipe' => $m['id_equipe1'], 'nom' => $m['nom_equipe1']];
                }
            }
        }

        // Éliminés en phase de poule : les 16 inscrits moins les 8 qui ont atteint les quarts
        $sqlAll = "SELECT e.id_equipe, e.nom
                   FROM inscription_tournoi it
                   JOIN equipe e ON e.id_equipe = it.id_equipe
                   WHERE it.id_tournoi = :id
                   ORDER BY e.nom ASC";
        $stmtAll = $pdo->prepare($sqlAll);
        $stmtAll->execute([':id' => (int)$id_tournoi]);
        $tous = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        $phase_poule = array_values(array_filter($tous, function ($e) use ($ids_quarts_participants) {
            return !in_array((int)$e['id_equipe'], $ids_quarts_participants, true);
        }));

        return [
            'vainqueur'        => $vainqueur,
            'finaliste'        => $finaliste,
            'demi_finalistes'  => $demi_finalistes,
            'quart_finalistes' => $quart_finalistes,
            'phase_poule'      => $phase_poule,
        ];
    }

    private static function genererQuarts($id_tournoi)
    {
        $pdo = database::Connexion();
        $classements = ModelMatch::getClassementsTournoi($id_tournoi);

        foreach (['A', 'B', 'C', 'D'] as $poule) {
            if (count($classements[$poule]) < 2) {
                return false;
            }
        }

        // Sans randomisation : branche fixe du tableau
        // Q1: 1A vs 2B, Q2: 1B vs 2A, Q3: 1C vs 2D, Q4: 1D vs 2C
        $appairements = [
            [
                'eq1' => (int)$classements['A'][0]['id_equipe'],
                'eq2' => (int)$classements['B'][1]['id_equipe'],
                'position' => 1,
            ],
            [
                'eq1' => (int)$classements['B'][0]['id_equipe'],
                'eq2' => (int)$classements['A'][1]['id_equipe'],
                'position' => 2,
            ],
            [
                'eq1' => (int)$classements['C'][0]['id_equipe'],
                'eq2' => (int)$classements['D'][1]['id_equipe'],
                'position' => 3,
            ],
            [
                'eq1' => (int)$classements['D'][0]['id_equipe'],
                'eq2' => (int)$classements['C'][1]['id_equipe'],
                'position' => 4,
            ],
        ];

        $pdo->beginTransaction();
        try {
            foreach ($appairements as $item) {
                $insMatch = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut)
                                           VALUES (:id_t, :eq1, :eq2, '1_4-finale', 'à_jouer')");
                $insMatch->execute([
                    ':id_t' => $id_tournoi,
                    ':eq1' => $item['eq1'],
                    ':eq2' => $item['eq2'],
                ]);

                $idMatch = (int)$pdo->lastInsertId();

                $insPhase = $pdo->prepare("INSERT INTO phase_finale (id_tournoi, id_match, phase, position_phase)
                                           VALUES (:id_t, :id_m, 'quart', :pos)");
                $insPhase->execute([
                    ':id_t' => $id_tournoi,
                    ':id_m' => $idMatch,
                    ':pos' => $item['position'],
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    private static function genererDemiSiPret($id_tournoi)
    {
        $pdo = database::Connexion();

        $quarts = self::getMatchsParPhase($id_tournoi, 'quart');
        if (count($quarts) !== 4) {
            return ['succes' => false, 'message' => null];
        }

        $messages = [];
        $creee = false;

        $result1 = self::creerDemiDepuisQuarts($id_tournoi, $quarts[0], $quarts[1], 1);
        if ($result1['succes']) {
            $creee = true;
            $messages[] = 'Demi-finale 1 générée.';
        } elseif (!empty($result1['message'])) {
            $messages[] = $result1['message'];
        }

        $result2 = self::creerDemiDepuisQuarts($id_tournoi, $quarts[2], $quarts[3], 2);
        if ($result2['succes']) {
            $creee = true;
            $messages[] = 'Demi-finale 2 générée.';
        } elseif (!empty($result2['message'])) {
            $messages[] = $result2['message'];
        }

        if ($creee) {
            return ['succes' => true, 'message' => implode(' ', $messages)];
        }

        return ['succes' => false, 'message' => empty($messages) ? null : implode(' ', $messages)];
    }

    private static function creerDemiDepuisQuarts($id_tournoi, array $quartA, array $quartB, $positionDemi)
    {
        $pdo = database::Connexion();

        if (self::phasePositionExiste($id_tournoi, 'demi', $positionDemi)) {
            return ['succes' => false, 'message' => null];
        }

        if ($quartA['statut'] !== 'terminé' || $quartB['statut'] !== 'terminé') {
            return ['succes' => false, 'message' => null];
        }

        $vainqueurA = self::getVainqueurRencontre($quartA);
        $vainqueurB = self::getVainqueurRencontre($quartB);

        if ($vainqueurA === null || $vainqueurB === null) {
            return ['succes' => false, 'message' => "Impossible de générer la demi-finale $positionDemi : un quart est nul."];
        }

        $pdo->beginTransaction();
        try {
            $insMatch = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut)
                                       VALUES (:id_t, :eq1, :eq2, '1_2-finale', 'à_jouer')");
            $insMatch->execute([
                ':id_t' => $id_tournoi,
                ':eq1' => $vainqueurA,
                ':eq2' => $vainqueurB,
            ]);

            $idMatch = (int)$pdo->lastInsertId();

            $insPhase = $pdo->prepare("INSERT INTO phase_finale (id_tournoi, id_match, phase, position_phase)
                                       VALUES (:id_t, :id_m, 'demi', :pos)");
            $insPhase->execute([
                ':id_t' => $id_tournoi,
                ':id_m' => $idMatch,
                ':pos' => $positionDemi,
            ]);

            $pdo->commit();
            return ['succes' => true, 'message' => null];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['succes' => false, 'message' => null];
        }
    }

    private static function genererFinaleSiPret($id_tournoi)
    {
        $pdo = database::Connexion();

        if (self::phasePositionExiste($id_tournoi, 'finale', 1)) {
            return ['succes' => false, 'message' => null];
        }

        $demis = self::getMatchsParPhase($id_tournoi, 'demi');
        if (count($demis) !== 2) {
            return ['succes' => false, 'message' => null];
        }

        if ($demis[0]['statut'] !== 'terminé' || $demis[1]['statut'] !== 'terminé') {
            return ['succes' => false, 'message' => null];
        }

        $vainqueur1 = self::getVainqueurRencontre($demis[0]);
        $vainqueur2 = self::getVainqueurRencontre($demis[1]);

        if ($vainqueur1 === null || $vainqueur2 === null) {
            return ['succes' => false, 'message' => 'Impossible de générer la finale : une demi-finale est nulle.'];
        }

        $pdo->beginTransaction();
        try {
            $insMatch = $pdo->prepare("INSERT INTO rencontre (id_tournoi, id_equipe1, id_equipe2, phase, statut)
                                       VALUES (:id_t, :eq1, :eq2, 'finale', 'à_jouer')");
            $insMatch->execute([
                ':id_t' => $id_tournoi,
                ':eq1' => $vainqueur1,
                ':eq2' => $vainqueur2,
            ]);

            $idMatch = (int)$pdo->lastInsertId();

            $insPhase = $pdo->prepare("INSERT INTO phase_finale (id_tournoi, id_match, phase, position_phase)
                                       VALUES (:id_t, :id_m, 'finale', 1)");
            $insPhase->execute([
                ':id_t' => $id_tournoi,
                ':id_m' => $idMatch,
            ]);

            $pdo->commit();
            return ['succes' => true, 'message' => 'La finale a été générée.'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['succes' => false, 'message' => null];
        }
    }

    private static function getMatchsParPhase($id_tournoi, $phase)
    {
        $pdo = database::Connexion();
        $sql = "SELECT pf.position_phase,
                       r.id_rencontre,
                       r.id_equipe1, r.id_equipe2,
                       r.buts_equipe1_final, r.buts_equipe2_final,
                       r.statut
                FROM phase_finale pf
                JOIN rencontre r ON r.id_rencontre = pf.id_match
                WHERE pf.id_tournoi = :id_t AND pf.phase = :phase
                ORDER BY pf.position_phase ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':phase' => $phase]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function phasePositionExiste($id_tournoi, $phase, $position)
    {
        $pdo = database::Connexion();
        $sql = "SELECT COUNT(*) FROM phase_finale
                WHERE id_tournoi = :id_t AND phase = :phase AND position_phase = :pos";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_t' => $id_tournoi, ':phase' => $phase, ':pos' => $position]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private static function getVainqueurRencontre(array $match)
    {
        $b1 = (int)$match['buts_equipe1_final'];
        $b2 = (int)$match['buts_equipe2_final'];

        if ($b1 > $b2) {
            return (int)$match['id_equipe1'];
        }
        if ($b2 > $b1) {
            return (int)$match['id_equipe2'];
        }

        return null;
    }
}
