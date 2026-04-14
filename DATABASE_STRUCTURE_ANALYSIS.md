# Database Structure Analysis - Tournament Application

## Database Information
- **Database Name:** `tournoi_five`
- **Database Status:** Not created yet (needs initialization)
- **Connection User:** root (no password)
- **Connection Host:** localhost

---

## Identified Tables (from Code Analysis)

### 1. TABLE: `joueur` (Players/Users)
**Purpose:** Store player/user information and authentication

**Fields Found in Code:**
- `id_joueur` - Primary key (integer, auto-increment)
- `user` - Username (string)
- `pw` - Password (string, appears to be hashed)
- `nom` - Last name (string)
- `prenom` - First name (string)
- `poste` - Position/Role (string - values: "Terrain", "Gardien", etc.)
- `niveau` - Level/Skill level (string - values: "A", "B", etc.)
- `id_equipe` - Foreign key to equipe table (integer, nullable)
- `is_admin` - Admin flag (boolean/integer - values: 0 or 1)

**SQL Examples:**
```sql
INSERT INTO joueur (user, pw, nom, prenom, poste, niveau, id_equipe) VALUES (?, ?, ?, ?, ?, ?, ?)
SELECT * FROM joueur WHERE id_joueur = :id
SELECT * FROM joueur WHERE user = :pseudo
UPDATE joueur SET id_equipe = :id_equipe WHERE id_joueur = :id_joueur
UPDATE joueur SET id_equipe = NULL WHERE id_joueur = :id_joueur
```

---

### 2. TABLE: `equipe` (Teams)
**Purpose:** Store team/club information

**Fields Found in Code:**
- `id_equipe` - Primary key (integer, auto-increment)
- `nom` - Team name (string)
- `code_acces` - Access code (string, 8 characters typically MD5)
- `id_createur` - Foreign key to joueur (captain/creator)
- `statut` - Status (string - values: "en_attente", "valide", "actif")
- `niveau` - Skill level (string - values: "A", "B", etc.)

**SQL Examples:**
```sql
INSERT INTO equipe (nom, code_acces, id_createur, statut) VALUES (?, ?, ?, ?)
INSERT INTO equipe (nom, niveau, code_acces, statut) VALUES (?, ?, ?, ?)
SELECT * FROM equipe WHERE statut = 'valide'
SELECT code_acces FROM equipe WHERE id_equipe = :id
UPDATE equipe SET statut = 'valide' WHERE id_equipe = :id
DELETE FROM equipe WHERE id_equipe = :id
UPDATE equipe SET nom = :nom WHERE id_equipe = :id
```

---

### 3. TABLE: `tournoi` (Tournaments)
**Purpose:** Store tournament/competition information

**Fields Found in Code:**
- `id_tournoi` - Primary key (integer, auto-increment)
- `nom` - Tournament name (string)
- `date_debut` - Start date (date - format: YYYY-MM-DD)
- `date_fin` - End date (date - format: YYYY-MM-DD)
- `statut` - Tournament status (string - values: "ouvert", "complet", "en_cours", "termine")

**SQL Examples:**
```sql
INSERT INTO tournoi (nom, date_debut, date_fin, statut) VALUES (?, ?, ?, ?)
SELECT * FROM tournoi WHERE id_tournoi = :id
UPDATE tournoi SET nom = :nom, date_debut = :debut, date_fin = :fin WHERE id_tournoi = :id
DELETE FROM tournoi WHERE id_tournoi = :id
UPDATE tournoi SET statut = 'complet' WHERE id_tournoi = :idT
UPDATE tournoi SET statut = :statut WHERE id_tournoi = :id
SELECT COUNT(*) FROM tournoi WHERE statut = 'complet'
```

---

### 4. TABLE: `inscription_tournoi` (Tournament Registrations)
**Purpose:** Many-to-many relationship between teams and tournaments, with pool assignments

**Fields Found in Code:**
- `id_equipe` - Foreign key to equipe (integer)
- `id_tournoi` - Foreign key to tournoi (integer)
- `poule` - Pool/Group assignment (string - values: "A", "B", "C", "D", nullable initially)
- `date_inscription` - Registration date (datetime - format: YYYY-MM-DD HH:MM:SS)

**Primary Key:** Composite (id_equipe + id_tournoi)

**SQL Examples:**
```sql
INSERT INTO inscription_tournoi (id_equipe, id_tournoi, poule, date_inscription) VALUES (?, ?, ?, NOW())
SELECT id_equipe FROM inscription_tournoi WHERE id_tournoi = :id
UPDATE inscription_tournoi SET poule = :poule WHERE id_tournoi = :id_t AND id_equipe = :id_e
DELETE FROM inscription_tournoi WHERE id_tournoi = ?
SELECT COUNT(*) FROM inscription_tournoi WHERE id_equipe = :idE AND id_tournoi = :idT
```

---

## Missing Features (Not Yet Implemented)

### ❌ Match/Game Tables
**No match-related tables found** - The following tables are NOT implemented yet:
- `rencontre` (matches/games) - NOT FOUND
- `match` - NOT FOUND
- `match_results` - NOT FOUND
- `game_stats` - NOT FOUND

This is notable because the statistics page mentions "Matchs joués" but there's no underlying database structure yet.

---

## Application Flow Summary

1. **User Registration**
   - Creates a `joueur` record (player)
   - Optionally assigns captain role

2. **Team Management**
   - Captain creates an `equipe` with access code
   - Other players join using code
   - Multiple players link to same `id_equipe`

3. **Tournament Workflow**
   - Admin creates `tournoi` with dates
   - Captains register teams via `inscription_tournoi`
   - When 16 teams registered, tournoi status becomes "complet"
   - Admin launches draw (tirage au sort) to assign pools (A, B, C, D)
   - Pool assignments stored in `inscription_tournoi.poule`

4. **Pool System**
   - 4 pools (A, B, C, D)
   - 4 teams per pool
   - Pools determined by random draw after 16 teams registered

---

## Database Creation Script Template

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS tournoi_five DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tournoi_five;

-- Create joueur table
CREATE TABLE joueur (
    id_joueur INT PRIMARY KEY AUTO_INCREMENT,
    user VARCHAR(50) UNIQUE NOT NULL,
    pw VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    poste VARCHAR(50),
    niveau VARCHAR(10),
    id_equipe INT NULL,
    is_admin TINYINT DEFAULT 0,
    FOREIGN KEY (id_equipe) REFERENCES equipe(id_equipe) ON DELETE SET NULL
);

-- Create equipe table
CREATE TABLE equipe (
    id_equipe INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    code_acces VARCHAR(8) NOT NULL UNIQUE,
    id_createur INT NOT NULL,
    statut ENUM('en_attente', 'valide', 'actif') DEFAULT 'en_attente',
    niveau VARCHAR(10),
    FOREIGN KEY (id_createur) REFERENCES joueur(id_joueur)
);

-- Create tournoi table
CREATE TABLE tournoi (
    id_tournoi INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('ouvert', 'complet', 'en_cours', 'termine') DEFAULT 'ouvert'
);

-- Create inscription_tournoi table
CREATE TABLE inscription_tournoi (
    id_equipe INT NOT NULL,
    id_tournoi INT NOT NULL,
    poule CHAR(1) NULL CHECK (poule IN ('A', 'B', 'C', 'D')),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_equipe, id_tournoi),
    FOREIGN KEY (id_equipe) REFERENCES equipe(id_equipe) ON DELETE CASCADE,
    FOREIGN KEY (id_tournoi) REFERENCES tournoi(id_tournoi) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX idx_joueur_equipe ON joueur(id_equipe);
CREATE INDEX idx_equipe_createur ON equipe(id_createur);
CREATE INDEX idx_inscription_tournoi ON inscription_tournoi(id_tournoi);
```

---

## Notes for Development

1. **No Match Tables Yet:** The statistics page is currently hardcoded and not connected to actual match data
2. **Foreign Key Constraints:** The code doesn't create `tournoi_five` database - needs manual setup
3. **Access Code Generation:** Generated using `strtoupper(substr(md5($nom . time()), 0, 8))`
4. **Password Storage:** Appears to use plain text in some cases (security issue - should use password hashing)
5. **Admin Flag:** Single boolean field in joueur table determines admin status
6. **Pool Assignment:** Randomized after registration, then stored in `inscription_tournoi.poule`

