DROP TABLE IF EXISTS phase_finale;

CREATE TABLE phase_finale (
    id_phase_finale INT AUTO_INCREMENT PRIMARY KEY,
    id_tournoi INT NOT NULL,
    id_match INT NOT NULL,
    phase ENUM('quart', 'demi', 'finale') NOT NULL,
    position_phase TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_phase_finale_match FOREIGN KEY (id_match) REFERENCES rencontre(id_rencontre) ON DELETE CASCADE,
    CONSTRAINT uq_phase_finale_unique_match UNIQUE (id_match),
    CONSTRAINT uq_phase_finale_phase_position UNIQUE (id_tournoi, phase, position_phase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
