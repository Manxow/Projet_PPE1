<?php
session_start();

echo "=== DEBUG SESSION ===\n";
echo "id_joueur: " . ($_SESSION['id_joueur'] ?? 'NOT SET') . "\n";
echo "idTeam: " . ($_SESSION['idTeam'] ?? 'NOT SET') . "\n";
echo "pseudo_joueur: " . ($_SESSION['pseudo_joueur'] ?? 'NOT SET') . "\n";

if (isset($_SESSION['idTeam'])) {
    require_once 'config/database.php';
    $pdo = database::Connexion();

    // Vérifier l'équipe
    $equipe = $pdo->query("SELECT * FROM equipe WHERE id_equipe = " . $_SESSION['idTeam'])->fetch();
    echo "\n=== ÉQUIPE ===\n";
    print_r($equipe);

    // Vérifier les matchs de cette équipe
    echo "\n=== MATCHS DE L'ÉQUIPE ===\n";
    $matchs = $pdo->query("SELECT * FROM rencontre WHERE (id_equipe1 = " . $_SESSION['idTeam'] . " OR id_equipe2 = " . $_SESSION['idTeam'] . ") AND id_tournoi = 8")->fetchAll();
    print_r($matchs);
}
