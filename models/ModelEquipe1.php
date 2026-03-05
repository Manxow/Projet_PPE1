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

    public static function getNomEquipe()
    {
        try {

            $sql = 'SELECT nom 
            FROM equipe 
            WHERE id_equipe = :idTeam';
            $idTeam = $_SESSION['idTeam'];

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
}
