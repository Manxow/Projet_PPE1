<?php
// Script pour générer un match de test
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ModelMatch.php';

try {
    $pdo = database::Connexion();

    echo "=== Génération d'un match de test ===\n\n";

    // Récupérer les équipes inscrites au tournoi 8 (notre tournoi de test)
    $sql = "SELECT id_equipe FROM inscription_tournoi WHERE id_tournoi = 8 ORDER BY RAND() LIMIT 2";
    $stmt = $pdo->query($sql);
    $equipes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($equipes) >= 2) {
        // Récupérer la poule de la première équipe
        $sql_poule = "SELECT poule FROM inscription_tournoi WHERE id_equipe = ? AND id_tournoi = 8";
        $stmt_poule = $pdo->prepare($sql_poule);
        $stmt_poule->execute([$equipes[0]]);
        $poule_result = $stmt_poule->fetch(PDO::FETCH_ASSOC);
        $id_poule = $poule_result ? $poule_result['poule'] : 'C';

        // Créer un match à jouer entre les deux équipes
        $sql_insert = "INSERT INTO rencontre (id_tournoi, id_poule, id_equipe1, id_equipe2, phase, statut) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);

        $result = $stmt_insert->execute([8, $id_poule, $equipes[0], $equipes[1], 'poule', 'à_jouer']);

        if ($result) {
            $id_match = $pdo->lastInsertId();

            // Récupérer les noms des équipes
            $sql_names = "SELECT e1.nom as eq1, e2.nom as eq2 
                         FROM equipe e1, equipe e2 
                         WHERE e1.id_equipe = ? AND e2.id_equipe = ?";
            $stmt_names = $pdo->prepare($sql_names);
            $stmt_names->execute([$equipes[0], $equipes[1]]);
            $teams = $stmt_names->fetch(PDO::FETCH_ASSOC);

            echo "✅ Match créé avec succès !\n";
            echo "ID du match : $id_match\n";
            echo "Équipe 1 (ID: {$equipes[0]}) : {$teams['eq1']}\n";
            echo "Équipe 2 (ID: {$equipes[1]}) : {$teams['eq2']}\n";
            echo "Poule : $id_poule\n";
            echo "Statut : à_jouer\n\n";
            echo "URL pour accéder au formulaire de saisie :\n";
            echo "http://localhost/Projet_PPE1/public/index.php?action=saisir_resultat&id_match=$id_match\n";
        } else {
            echo "❌ Erreur lors de la création du match\n";
        }
    } else {
        echo "❌ Pas assez d'équipes trouvées au tournoi 8\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}
