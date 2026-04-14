<?php
// Script de création d'un tournoi de test avec 16 équipes et matchs
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ModelTournoi.php';
require_once __DIR__ . '/models/ModelEquipe.php';
require_once __DIR__ . '/models/ModelMatch.php';

try {
    $pdo = database::Connexion();

    echo "=== Création d'un tournoi de test avec 16 équipes et matchs ===\n\n";

    // 1. Créer 16 équipes de test
    echo "1. Création de 16 équipes de test...\n";

    $noms_equipes = [
        "Les Aigles",
        "Les Dragons",
        "Les Titans",
        "Les Phénix",
        "Les Loups",
        "Les Requins",
        "Les Faucons",
        "Les Ours",
        "Les Lions",
        "Les Chevaliers",
        "Les Guerriers",
        "Les Samouraïs",
        "Les Spartans",
        "Les Centaures",
        "Les Griffons",
        "Les Cyclopes"
    ];

    $equipes_creees = [];

    foreach ($noms_equipes as $nom) {
        // Vérifier si l'équipe existe déjà
        $check = $pdo->prepare("SELECT id_equipe FROM equipe WHERE nom = ?");
        $check->execute([$nom]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo "  ✓ Équipe '{$nom}' existe déjà (ID: {$existing['id_equipe']})\n";
            $equipes_creees[] = $existing['id_equipe'];
        } else {
            // Créer l'équipe
            $code_access = strtoupper(substr(md5($nom . time()), 0, 8));
            $sql = "INSERT INTO equipe (nom, niveau, code_acces, statut) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, "A", $code_access, "actif"]);
            $id = $pdo->lastInsertId();
            echo "  ✓ Équipe '{$nom}' créée (ID: {$id})\n";
            $equipes_creees[] = $id;
        }
    }

    // 2. Créer un tournoi
    echo "\n2. Création du tournoi de test...\n";

    $nom_tournoi = "Tournoi de Test - " . date('d/m/Y H:i:s');
    $debut = date('Y-m-d');
    $fin = date('Y-m-d', strtotime('+7 days'));

    // Vérifier si un tournoi de test existe déjà
    $check = $pdo->prepare("SELECT id_tournoi FROM tournoi WHERE nom LIKE 'Tournoi de Test%' ORDER BY id_tournoi DESC LIMIT 1");
    $check->execute();
    $existing_tournoi = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing_tournoi) {
        $id_tournoi = $existing_tournoi['id_tournoi'];
        echo "  ✓ Réutilisation du tournoi existant (ID: {$id_tournoi})\n";

        // Supprimer les inscriptions existantes
        $del = $pdo->prepare("DELETE FROM inscription_tournoi WHERE id_tournoi = ?");
        $del->execute([$id_tournoi]);
    } else {
        $sql = "INSERT INTO tournoi (nom, date_debut, date_fin, statut) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom_tournoi, $debut, $fin, 'complet']);
        $id_tournoi = $pdo->lastInsertId();
        echo "  ✓ Tournoi créé (ID: {$id_tournoi})\n";
        echo "    - Nom: {$nom_tournoi}\n";
        echo "    - Dates: {$debut} au {$fin}\n";
    }

    // 3. Inscrire les 16 équipes au tournoi
    echo "\n3. Inscription des 16 équipes au tournoi...\n";

    $poules = ['A', 'B', 'C', 'D'];
    $index = 0;

    foreach ($equipes_creees as $id_equipe) {
        $poule = $poules[$index % 4]; // Répartition automatique : 4 équipes par poule

        $sql = "INSERT INTO inscription_tournoi (id_equipe, id_tournoi, poule, date_inscription) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_equipe, $id_tournoi, $poule]);

        echo "  ✓ Équipe ID {$id_equipe} inscrite à la poule {$poule}\n";
        $index++;
    }

    // 4. Générer les matchs des poules
    echo "\n4. Génération des matchs des poules...\n";

    $poules = ['A', 'B', 'C', 'D'];
    $total_matchs = 0;

    foreach ($poules as $poule) {
        $sql_count = "SELECT COUNT(*) FROM rencontre WHERE id_tournoi = ? AND id_poule = ?";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute([$id_tournoi, $poule]);
        $existing_matchs = $stmt_count->fetchColumn();

        if ($existing_matchs == 0) {
            ModelMatch::genererMatchsPoule($id_tournoi, $poule);
            $sql_count = "SELECT COUNT(*) FROM rencontre WHERE id_tournoi = ? AND id_poule = ?";
            $stmt_count = $pdo->prepare($sql_count);
            $stmt_count->execute([$id_tournoi, $poule]);
            $nb_matchs = $stmt_count->fetchColumn();
            echo "  ✓ Poule {$poule} : {$nb_matchs} matchs créés\n";
            $total_matchs += $nb_matchs;
        } else {
            echo "  ✓ Poule {$poule} : {$existing_matchs} matchs existants\n";
            $total_matchs += $existing_matchs;
        }
    }

    echo "\n=== ✓ SUCCÈS ! ===\n";
    echo "Tournoi créé avec ID: {$id_tournoi}\n";
    echo "16 équipes réparties dans 4 poules (A, B, C, D) - 4 équipes par poule\n";
    echo "Total: {$total_matchs} matchs générés pour la phase de poule\n\n";
    echo "Vous pouvez maintenant accéder au tournoi à l'adresse:\n";
    echo "http://localhost/Projet_PPE1/public/index.php?action=tournoi\n";
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
