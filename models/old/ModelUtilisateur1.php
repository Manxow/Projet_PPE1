<?php

require_once '../config/database.php';

class ModelUtilisateur
{
    //ATTRIBUTS

    private int $_id_utilisateur;
    private string $_nom_utilisateur;
    private string $_password;



    //CONSTRUCT


    public function __construct($nom_utilisateur, $password)
    {
        $this->_nom_utilisateur = $nom_utilisateur;
        $this->_password = $password;
    }


    //SETTERS

    public function setId(string $id)
    {
        $this->_id_utilisateur = $id;
    }

    public function setPassword(string $password)
    {
        $this->_password = $password;
    }

    public function setNomUtilisateur(string $nom)
    {
        $this->_nom_utilisateur = $nom;
    }

    //GETTERS

    public function getIdUtilisateur()
    {
        return $this->_id_utilisateur;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getNomUtilisateur()
    {
        return $this->_nom_utilisateur;
    }


    //AJOUTER UTILISATEUR (DANS BASE DE DONNEES)

    public function AjouterUtilisateur()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'INSERT INTO utilisateur (nom_utilisateur,pw) 
                    VALUES (:nom_utilisateur,:pw)';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $nom_utilisateur = $this->getNomUtilisateur();
            $pw = $this->getPassword();


            // CORRECTION ICI : On utilise les noms, pas les chiffres
            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->bindParam(':pw', $pw);


            $exec = $stmt->execute();
            echo ('total insertion est ' . $exec);
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
        $pdo = null;
    }

    public function getUnUser()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'SELECT *
            FROM utilisateur
            WHERE nom_utilisateur = :nom_utilisateur
            AND pw = :pw';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $nom_utilisateur = $this->getNomUtilisateur();
            $pw = $this->getPassword();


            // CORRECTION ICI : On utilise les noms, pas les chiffres
            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->bindParam(':pw', $pw);

            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return 1;
            } else {
                return 0;
            }
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }

    public function getMonId()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'SELECT id_utilisateur
            FROM utilisateur
            WHERE nom_utilisateur = :nom_utilisateur
            AND pw = :pw';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $nom_utilisateur = $this->getNomUtilisateur();
            $pw = $this->getPassword();


            // CORRECTION ICI : On utilise les noms, pas les chiffres
            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->bindParam(':pw', $pw);

            $stmt->execute();

            $user = $stmt->fetchColumn();

            return $user;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }

    public function getUnJoueurFromUser()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'SELECT j.*
            FROM joueur j, utilisateur u
            WHERE j.id_utilisateur = :id_User';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $id_User = $this->getIdUtilisateur();

            $stmt->bindParam(':id_User', $id_User);

            $stmt->execute();

            $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

            return $joueur;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }
}
