<?php

class database
{

    private static $host;
    private static $dbname;
    private static $user;
    private static $password;

    public static function Connexion()
    {
        $host = 'localhost';
        $dbname = 'tournoi_five1';
        $user = 'root';
        $password = '';
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }

        return $pdo;
    }
}
