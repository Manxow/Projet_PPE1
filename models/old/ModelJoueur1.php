<?php

require_once '../config/database.php';

class ModelJoueur
{
    //ATTRIBUTS

    private int $_id_joueur;
    private string $_nom;
    private string $_prenom;
    private string $_poste;
    private string $_niveau;
    private int $_id_equipe;
    private int $_id_utilisateur;



    //CONSTRUCT


    public function __construct($n, $pren, $post, $lvl, $team = null)
    {

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

    public function setIdUtilisateur($id)
    {
        $this->_id_utilisateur = $id;
    }

    //GETTERS

    public function getIdJoueur()
    {
        return $this->_id_joueur;
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

    public function getIdUtilisateur()
    {
        return $this->_id_utilisateur;
    }

    //AJOUTER UTILISATEUR (DANS BASE DE DONNEES)

    public function AjouterJoueur()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'INSERT INTO joueur (nom, prenom, poste, niveau, id_equipe, id_utilisateur) 
                    VALUES (:nom, :prenom, :poste, :niveau, :equipe, :utilisateur)';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);


            $nom = $this->getNom();
            $prenom = $this->getPrenom();
            $poste = $this->getPoste();
            $niveau = $this->getNiveau();
            $equipe = $this->getEquipe();
            $utilisateur = $this->getIdUtilisateur();

            // CORRECTION ICI : On utilise les noms, pas les chiffres

            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':poste', $poste);
            $stmt->bindParam(':niveau', $niveau);

            // Pour les entiers, on précise le type comme demandé
            $stmt->bindParam(':equipe', $equipe, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur', $utilisateur, PDO::PARAM_INT);

            $exec = $stmt->execute();
            echo ('total insertion est ' . $exec);
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
        $pdo = null;
    }

    public function SelectIdUser()
    {

        try {
            $requete = 'SELECT id_utilisateur 
            FROM utilisateur 
            WHERE nom_utilisateur = :user';

            $user = $_SESSION['nom_utilisateur'];

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $stmt->bindParam(':user', $user, PDO::PARAM_STR);

            $stmt->execute();
            $new_id = $stmt->fetchColumn();
            return $new_id;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
        # $pdo = null; --> ne passe pas dessus car s'arrête au 'return' fermeture automatique de PDO en fin de script
    }
}
