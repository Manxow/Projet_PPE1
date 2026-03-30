<?php

class database
{
    // La variable secrète qui va garder la connexion en mémoire
    private static $instance = null;

    public static function Connexion()
    { {
            // Si la connexion n'existe pas encore, on la crée
            if (self::$instance === null) {

                $host = 'localhost';
                $dbname = 'tournoi_five';
                $user = 'root';
                $password = '';

                try {
                    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
                    // On active les erreurs PDO pour faciliter le débogage
                    self::$instance = new PDO($dsn, $user, $password);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Optionnel mais très pratique : récupérer les résultats sous forme de tableau associatif par défaut
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die('Erreur de connexion à la base de données : ' . $e->getMessage());
                }
            }

            // On retourne la connexion (nouvelle ou existante)
            return self::$instance;
        }
    }
}
