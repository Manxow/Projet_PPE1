<?php

require_once '../config/database.php';

class ModelEquipe
{
    //Attributs

    private int $_id_equipe;
    private string $_nom;
    private string $_niveau;
    private int $_id_poule;

    //Constructs

    public function __construct($id_equipe, $nom = null, $niveau = null, $id_poule = null)
    {
        $this->_id_equipe = $id_equipe;
        if ($nom) {
            $this->_nom = $nom;
        }
        if ($niveau) {
            $this->_niveau = $niveau;
        }
        if ($id_poule) {
            $this->_id_poule = $id_poule;
        }
    }

    //Setters

    public function setId($id_equipe)
    {
        $this->_id_equipe = $id_equipe;
    }

    public function setNom($nom)
    {
        $this->_nom = $nom;
    }

    public function setNiveau($niveau)
    {
        $this->_niveau = $niveau;
    }

    public function setPoule($id_poule)
    {
        $this->_id_poule = $id_poule;
    }

    //Getters

    public function getId()
    {
        return $this->_id_equipe;
    }

    public function getNom()
    {
        return $this->_nom;
    }

    public function getNiveau()
    {
        return $this->_niveau;
    }

    public function getPoule()
    {
        return $this->_id_poule;
    }

    public static function getAllEquipes()
    {
        $pdo = database::Connexion();

        $sql = "SELECT id_equipe, nom 
        FROM equipe 
        ORDER BY nom ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getNomEquipe($idTeam)
    {
        try {

            $sql = 'SELECT nom 
            FROM equipe 
            WHERE id_equipe = :idTeam';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idTeam', $idTeam, PDO::PARAM_INT);
            $stmt->execute();

            $team = $stmt->fetchColumn();
            return $team;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }

    public static function getCodeAcces($id_equipe)
    {
        // On récupère la connexion PDO (adapte selon ta configuration exacte)
        $pdo = database::Connexion();

        $sql = "SELECT code_acces FROM equipe WHERE id_equipe = :id";

        $stmt = $pdo->prepare($sql);
        // Méthode explicite et fortement typée
        $stmt->bindValue(':id', $id_equipe, PDO::PARAM_INT);
        $stmt->execute();

        // Méthode raccourcie (gardée en mémoire pour l'exemple)
        // $stmt->execute(['id' => $id_equipe]); 

        $result = $stmt->fetch();

        return $result ? $result['code_acces'] : null;
    }

    public static function getEquipesValidees()
    {
        // 1. On récupère la connexion optimisée
        $pdo = database::Connexion();

        // 2. On sélectionne uniquement les équipes validées, triées par ordre alphabétique
        $sql = "SELECT * FROM equipe WHERE statut = 'valide' ORDER BY nom ASC";

        // 3. On prépare et on exécute
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // On retourne tous les résultats (fetchAll car il y a plusieurs équipes)
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEquipeById($id_equipe)
    {
        $pdo = database::Connexion();
        $sql = "SELECT * FROM equipe WHERE id_equipe = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id_equipe, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function creerEquipe($nom, $code_acces, $id_createur)
    {
        $pdo = database::Connexion();

        // On insère l'équipe. Par défaut, on peut imaginer qu'elle n'est pas encore validée (valide = 0)
        $sql = "INSERT INTO equipe (nom, code_acces, id_createur, statut) 
                VALUES (:nom, :code_acces, :id_createur, 'en_attente')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':code_acces', $code_acces);
        $stmt->bindValue(':id_createur', $id_createur, PDO::PARAM_INT);

        $stmt->execute();

        // On retourne l'ID de la nouvelle équipe qui vient d'être généré par la base de données !
        return $pdo->lastInsertId();
    }

    /*----------------------------------------------------------------- */
    /*---SECTION POUR LE PANEL ADMIN - GESTION DES ÉQUIPES EN ATTENTE---*/
    /*----------------------------------------------------------------- */

    // Récupérer la liste des équipes en attente
    public static function getEquipesEnAttente()
    {
        $pdo = database::Connexion();
        $sql = "SELECT * FROM equipe WHERE statut = 'en_attente'";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // L'admin dit OUI : on passe le statut à 'valide'
    public static function validerEquipe($id_equipe)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE equipe SET statut = 'valide' WHERE id_equipe = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id_equipe]);
    }

    // L'admin dit NON : on supprime l'équipe et on libère les joueurs
    public static function refuserEquipe($id_equipe)
    {
        $pdo = database::Connexion();

        // 1. On remet tous les joueurs de cette équipe en "Sans équipe" (NULL)
        $sqlJoueurs = "UPDATE joueur SET id_equipe = NULL WHERE id_equipe = :id";
        $stmtJ = $pdo->prepare($sqlJoueurs);
        $stmtJ->execute([':id' => $id_equipe]);

        // 2. On supprime définitivement l'équipe refusée
        $sqlEquipe = "DELETE FROM equipe WHERE id_equipe = :id";
        $stmtE = $pdo->prepare($sqlEquipe);
        return $stmtE->execute([':id' => $id_equipe]);
    }


    // Compter le nombre d'équipes en attente de validation
    public static function getNbEquipesEnAttente()
    {
        $pdo = database::Connexion();
        $sql = "SELECT COUNT(*) FROM equipe WHERE statut = 'en_attente'";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    }


    // Mettre à jour le nom d'une équipe
    public static function modifierEquipe($id_equipe, $nom)
    {
        $pdo = database::Connexion();
        $sql = "UPDATE equipe SET nom = :nom WHERE id_equipe = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $nom,
            ':id'  => $id_equipe
        ]);
    }
}
