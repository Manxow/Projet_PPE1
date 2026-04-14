<?php
// Script de création des tables pour la gestion des matchs
require_once __DIR__ . '/config/database.php';

try {
    $pdo = database::Connexion();

    echo "=== Création des tables de matchs ===\n\n";

    // 1. Table rencontre (matchs)
    $sql_rencontre = "CREATE TABLE IF NOT EXISTS rencontre (
        id_rencontre INT PRIMARY KEY AUTO_INCREMENT,
        id_tournoi INT NOT NULL,
        id_poule CHAR(1) DEFAULT NULL,  -- A, B, C, D (NULL pour phases finales)
        id_equipe1 INT NOT NULL,
        id_equipe2 INT NOT NULL,
        date_match DATETIME DEFAULT NULL,
        statut VARCHAR(20) DEFAULT 'à_jouer',  -- 'à_jouer', 'terminé', 'erreur'
        phase VARCHAR(50) DEFAULT 'poule',  -- 'poule', '1_4-finale', '1_2-finale', 'finale'
        
        -- Résultats saisis par les deux capitaines
        buts_equipe1_saisie1 INT DEFAULT NULL,
        buts_equipe2_saisie1 INT DEFAULT NULL,
        buts_equipe1_saisie2 INT DEFAULT NULL,
        buts_equipe2_saisie2 INT DEFAULT NULL,
        
        -- Résultat final (si validé)
        buts_equipe1_final INT DEFAULT NULL,
        buts_equipe2_final INT DEFAULT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (id_tournoi) REFERENCES tournoi(id_tournoi),
        FOREIGN KEY (id_equipe1) REFERENCES equipe(id_equipe),
        FOREIGN KEY (id_equipe2) REFERENCES equipe(id_equipe)
    )";

    $pdo->exec($sql_rencontre);
    echo "✓ Table 'rencontre' créée/vérifiée\n";

    // 2. Table des phases finales (schema officiel)
    $sql_phase_finale = "CREATE TABLE IF NOT EXISTS phase_finale (
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

    $pdo->exec($sql_phase_finale);
    echo "✓ Table 'phase_finale' créée/vérifiée\n";

    echo "\n=== Tables créées avec succès ! ===\n";
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}
