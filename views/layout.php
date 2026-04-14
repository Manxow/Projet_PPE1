<?php
// On vérifie de manière universelle si une session joueur existe
$estConnecte = isset($_SESSION['id_joueur']);

// --- CALCUL DES NOTIFICATIONS ADMIN ---
$nbEquipesNotif = 0;
$nbTournoisNotif = 0;
$totalNotifs = 0;

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    require_once __DIR__ . '/../models/ModelEquipe.php';
    require_once __DIR__ . '/../models/ModelTournoi.php';

    $nbEquipesNotif = ModelEquipe::getNbEquipesEnAttente();
    $nbTournoisNotif = ModelTournoi::getNbTournoisComplets();
    $totalNotifs = $nbEquipesNotif + $nbTournoisNotif;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Five M2L</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="<?= isset($typePage) && $typePage === 'auth' ? 'page-auth' : '' ?>">

    <header>
        <div class="logo-container">
            <a href="index.php?action=accueil" class="logo">FM</a>
        </div>

        <div class="titre-container">
            <h1><span class="ballon">⚽</span> Five M2L <span class="ballon">⚽</span></h1>
        </div>

        <div class="auth-container">
            <?php if ($typePage === 'auth'): ?>
                <a href="index.php?action=accueil" class="btn">Retour à l'accueil</a>
            <?php else: ?>
                <?php if (!$estConnecte): ?>
                    <a href="index.php?action=connexion" class="btn">Se connecter</a>
                    <a href="index.php?action=inscription" class="btn">S'inscrire</a>
                <?php else: ?>
                    <span class="pseudo-header">
                        <span class="pseudo-nom">👤 <?= isset($_SESSION['pseudo_joueur']) ? htmlspecialchars($_SESSION['pseudo_joueur']) : 'Mon Profil' ?></span>
                        <?php if (isset($_SESSION['nomTeam']) && $_SESSION['nomTeam']): ?>
                            <span class="pseudo-equipe">🛡️ <?= htmlspecialchars($_SESSION['nomTeam']) ?></span>
                        <?php endif; ?>
                    </span>
                    <a href="index.php?action=deconnexion" class="btn">Déconnexion</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($typePage === 'onglet'): ?>
        <nav class="barre-onglets">
            <a href="index.php?action=accueil" class="onglet <?= $action == 'accueil' ? 'actif' : '' ?>">📰 Accueil</a>

            <a href="index.php?action=equipe" class="onglet <?= $action == 'equipe' ? 'actif' : '' ?>">
                👥 Équipe
                <?php if ($nbEquipesNotif > 0): ?>
                    <span class="badge-notification"><?= $nbEquipesNotif ?></span>
                <?php endif; ?>
            </a>

            <a href="index.php?action=tournoi" class="onglet <?= $action == 'tournoi' ? 'actif' : '' ?>">
                🏆 Tournoi
                <?php if ($nbTournoisNotif > 0): ?>
                    <span class="badge-notification"><?= $nbTournoisNotif ?></span>
                <?php endif; ?>
            </a>

            <a href="index.php?action=statistiques" class="onglet <?= $action == 'statistiques' ? 'actif' : '' ?>">📈 Statistiques</a>

            <?php if ($estConnecte): ?>
                <a href="index.php?action=profile" class="onglet <?= $action == 'profile' ? 'actif' : '' ?>">👤 Mon Profil</a>

                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="index.php?action=admin_panel" class="onglet onglet-admin <?= $action == 'admin_panel' ? 'actif' : '' ?>">
                        👑 Panel Admin
                        <?php if ($totalNotifs > 0): ?>
                            <span class="badge-notification"><?= $totalNotifs ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

            <?php endif; ?>

        </nav>
    <?php endif; ?>

    <main class="contenu">
        <?php
        // L'inclusion depuis la racine du projet
        require_once __DIR__ . '/' . $fichierVue;
        ?>
    </main>

</body>

</html>