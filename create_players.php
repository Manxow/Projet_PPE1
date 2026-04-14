<?php
require_once 'config/database.php';

$pdo = database::Connexion();

// Récupérer toutes les équipes avec id >= 21
$query = "SELECT id_equipe, nom FROM equipe WHERE id_equipe >= 21 ORDER BY id_equipe";
$stmt = $pdo->query($query);
$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($equipes)) {
    echo "Aucune équipe trouvée avec l'id >= 21\n";
    exit;
}

// Les postes disponibles
$postes = ['Attaquant', 'Milieu', 'Défenseur', 'Gardien'];
$niveaux = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];

// Le mot de passe hashé
$mdpHashe = password_hash("Joueur@2024", PASSWORD_DEFAULT);

$playersCount = 0;
foreach ($equipes as $equipe) {
    $id_equipe = $equipe['id_equipe'];
    $nom_equipe = $equipe['nom'];

    // Pour chaque équipe, créer 5 joueurs
    for ($i = 1; $i <= 5; $i++) {
        $poste = $postes[($i - 1) % count($postes)];
        $niveau = $niveaux[array_rand($niveaux)];
        $user = "Joueur_" . $id_equipe . "_" . $i;
        $nom = "Nom" . $i;
        $prenom = "Prenom" . $i;

        // Le premier joueur est le capitaine
        $est_capitaine = ($i === 1) ? 1 : 0;

        $insertQuery = "INSERT INTO joueur (user, nom, prenom, pw, poste, niveau, id_equipe) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            $user,
            $nom,
            $prenom,
            $mdpHashe,
            $poste,
            $niveau,
            $id_equipe
        ]);

        if ($est_capitaine) {
            // Mettre à jour l'équipe avec le capitaine
            $updateQuery = "UPDATE equipe SET id_createur = ? WHERE id_equipe = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([
                $pdo->lastInsertId(),
                $id_equipe
            ]);
        }

        $playersCount++;
    }

    echo "✓ {$nom_equipe} (ID {$id_equipe}): 5 joueurs créés\n";
}

echo "\n✅ Succès! {$playersCount} joueurs créés en total\n";
echo "📋 Mot de passe pour tous: Joueur@2024\n";
