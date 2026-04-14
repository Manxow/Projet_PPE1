<?php

require_once __DIR__ . '/../config/database.php';

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
    private ?int $_id_equipe = null;




    //CONSTRUCT


    public function __construct($n_u, $pw, $n, $pren, $post, $lvl, $team)
    {

        $this->_nom_utilisateur = $n_u;
        $this->_password = $pw;
        $this->_nom = $n;
        $this->_prenom = $pren;
        $this->_poste = $post;
        $this->_niveau = $lvl;
        $this->_id_equipe = $team;
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
            $requete = 'INSERT INTO joueur (user, pw, nom, prenom, poste, niveau, id_equipe) 
                    VALUES (:nom_utilisateur, :pw, :nom, :prenom, :poste, :niveau, :equipe)';

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
            return $exec;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
        $pdo = null;
    }


    public static function getUnJoueur($identifiant, $mdp)
    {
        try {
            $requete = 'SELECT *
            FROM joueur
            WHERE user = :nom_utilisateur
            AND pw = :pw';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $nom_utilisateur = $_SESSION['nom_utilisateur'] ?? null;
            $pw = $_SESSION['pw'] ?? null;

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

    public static function getJoueurByPseudo($pseudo)
    {
        try {
            $requete = 'SELECT id_joueur, user, pw, is_admin, id_equipe
            FROM joueur
            WHERE user = :pseudo';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $stmt->bindParam(':pseudo', $pseudo);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }

    public static function getPwById($id_joueur)
    {
        try {
            $requete = 'SELECT pw
            FROM joueur
            WHERE id_joueur = :id';

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $stmt->bindParam(':id', $id_joueur, PDO::PARAM_INT);

            $stmt->execute();

            $pw = $stmt->fetchColumn();

            return $pw;
        } catch (PDOException $e) {
            echo ('Erreur de connexion: ' . $e->getMessage());
            exit();
        }
    }

    public function getMonId()
    {
        try {
            // La requête SQL utilise des marqueurs nommés (:quelquechose)
            $requete = 'SELECT id_joueur
            FROM joueur
            WHERE user = :nom_utilisateur
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
    public static function getJoueurById($id_joueur)
    {

        try {
            $requete = "SELECT * FROM joueur WHERE id_joueur = :id";
            // 1. Connexion à la base de données 
            // ⚠️ À ADAPTER : Si tu as déjà un fichier de connexion (ex: database.php), 
            // utilise ta variable existante au lieu de recréer un nouveau PDO ici.
            $pdo = database::Connexion();

            // On demande à PDO d'afficher les erreurs s'il y en a
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. Préparation de la requête (Le :id est un paramètre sécurisé)
            $stmt = $pdo->prepare($requete);
            $stmt->bindParam(':id', $id_joueur, PDO::PARAM_INT);

            // 3. Exécution de la requête en lui donnant la vraie valeur de l'ID
            $stmt->execute();

            // 4. On récupère le résultat sous forme de tableau associatif
            // FETCH_ASSOC permet d'avoir $joueur['nom'] au lieu de $joueur[1]
            $mon_joueur = $stmt->fetch(PDO::FETCH_ASSOC);

            // 5. On renvoie le joueur au Contrôleur (ou "false" si l'ID n'existe pas)
            return $mon_joueur;
        } catch (Exception $e) {
            // S'il y a un problème avec la base de données, on affiche l'erreur
            die('Erreur SQL : ' . $e->getMessage());
        }
    }

    // méthode de mise à jour du profil
    public static function updateJoueur($id_joueur, $user, $nom, $prenom, $poste, $niveau, $equipe)
    {
        try {
            $requete = "UPDATE joueur
                        SET user = :user,
                            nom = :nom,
                            prenom = :prenom,
                            poste = :poste,
                            niveau = :niveau,
                            id_equipe = :equipe
                        WHERE id_joueur = :id";

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $stmt->bindParam(':user', $user);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':poste', $poste);
            $stmt->bindParam(':niveau', $niveau);
            $stmt->bindParam(':equipe', $equipe, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id_joueur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            die('Erreur SQL : ' . $e->getMessage());
        }
    }

    public static function updatePassword($id_joueur, $newPassword)
    {
        try {
            $requete = "UPDATE joueur
                        SET pw = :newPassword
                        WHERE id_joueur = :id";

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);

            $stmt->bindParam(':newPassword', $newPassword);
            $stmt->bindParam(':id', $id_joueur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            die('Erreur SQL : ' . $e->getMessage());
        }
    }

    public static function updateEquipeJoueur($id_joueur, $id_equipe)
    {
        $db = database::Connexion();
        // On met à jour uniquement la colonne id_equipe pour ce joueur
        $sql = "UPDATE joueur SET id_equipe = :id_equipe WHERE id_joueur = :id_joueur";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_equipe', $id_equipe, PDO::PARAM_INT);
        $stmt->bindValue(':id_joueur', $id_joueur, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function quitterEquipe($id_joueur)
    {
        $db = database::Connexion();
        // On force la valeur à NULL directement dans la requête
        $sql = "UPDATE joueur SET id_equipe = NULL WHERE id_joueur = :id_joueur";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_joueur', $id_joueur, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function getJoueursParEquipe($id_equipe)
    {
        try {
            $requete = "SELECT id_joueur, user, nom, prenom, poste, niveau 
                        FROM joueur 
                        WHERE id_equipe = :id_equipe
                        ORDER BY nom ASC, prenom ASC";

            $pdo = database::Connexion();
            $stmt = $pdo->prepare($requete);
            $stmt->bindParam(':id_equipe', $id_equipe, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Erreur SQL : ' . $e->getMessage());
        }
    }
}
