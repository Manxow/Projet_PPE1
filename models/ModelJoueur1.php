<?php

require_once '../config/database.php';

class ModelJoueur
{
    //ATTRIBUTS

    private int $_id_joueur;
    private string $_nom_utilisateur;
    private string $_password;
    private string $_nom;
    private string $_prenom;
    private string $_poste;
    private string $_niveau;
    private int $_id_equipe;




    //CONSTRUCT


    public function __construct($n_u, $pw, $n, $pren, $post, $lvl, $team = null)
    {

        $this->_nom_utilisateur = $n_u;
        $this->_password = $pw;
        $this->_nom = $n;
        $this->_prenom = $pren;
        $this->_poste = $post;
        $this->_niveau = $lvl;

        if ($team != null) {
            $this->_id_equipe = $team;
        } else {
            $this->_id_equipe == null;
        }
    }


    //SETTERS

    public function setId($id)
    {
        $this->_id_joueur = $id;
    }

    public function setPassword(string $password)
    {
        $this->_password = $password;
    }

    public function setNomUtilisateur(string $nom)
    {
        $this->_nom_utilisateur = $nom;
    }


    public function setNom($name)
    {
        $this->_nom = $name;
    }

    public function setPrenom($pren)
    {
        $this->_prenom = $pren;
    }

    public function setPoste($post)
    {
        $this->_poste = $post;
    }

    public function setNiveau($lvl)
    {
        $this->_niveau = $lvl;
    }

    public function setEquipe($team)
    {
        $this->_id_equipe = $team;
    }

    //GETTERS

    public function getIdJoueur()
    {
        return $this->_id_joueur;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getNomUtilisateur()
    {
        return $this->_nom_utilisateur;
    }

    public function getNom()
    {
        return $this->_nom;
    }

    public function getPrenom()
    {
        return $this->_prenom;
    }

    public function getPoste()
    {
        return $this->_poste;
    }

    public function getNiveau()
    {
        return $this->_niveau;
    }

    public function getEquipe()
    {
        return $this->_id_equipe;
    }



    //AJOUTER UTILISATEUR (DANS BASE DE DONNEES)

    public function AjouterJoueur()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'INSERT INTO joueur (user, pwn nom, prenom, poste, niveau, id_equipe, id_utilisateur) 
                    VALUES (:nom_utilisateur, :pw, :nom, :prenom, :poste, :niveau, :equipe, :utilisateur)';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $nom_utilisateur = $this->getNomUtilisateur();
            $pw = $this->getPassword();
            $nom = $this->getNom();
            $prenom = $this->getPrenom();
            $poste = $this->getPoste();
            $niveau = $this->getNiveau();
            $equipe = $this->getEquipe();

            // CORRECTION ICI : On utilise les noms, pas les chiffres

            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->bindParam(':pw', $pw);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':poste', $poste);
            $stmt->bindParam(':niveau', $niveau);

            // Pour les entiers, on précise le type comme demandé
            $stmt->bindParam(':equipe', $equipe, PDO::PARAM_INT);

            $exec = $stmt->execute();
            echo ('total insertion est ' . $exec);
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
        $pdo = null;
    }
}
