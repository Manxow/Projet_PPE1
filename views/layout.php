<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Five M2L</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="<?= $typePage === 'auth' ? 'page-auth' : '' ?>">

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
                    <span style="margin-right: 15px; font-weight: bold;">Mon Profil</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($typePage === 'onglet'): ?>
        <nav class="barre-onglets">
            <a href="index.php?action=accueil" class="onglet <?= $action == 'accueil' ? 'actif' : '' ?>">Accueil</a>
            <a href="index.php?action=equipe" class="onglet <?= $action == 'equipe' ? 'actif' : '' ?>">Équipe</a>
            <a href="index.php?action=tournoi" class="onglet <?= $action == 'tournoi' ? 'actif' : '' ?>">Tournoi</a>
            <a href="index.php?action=statistiques" class="onglet <?= $action == 'statistiques' ? 'actif' : '' ?>">Statistiques</a>
        </nav>
    <?php endif; ?>

    <main class="contenu">
        <?php
        // L'inclusion depuis la racine du projet
        echo "Inclusion de la vue : " . __DIR__ . '/' . $fichierVue . "<br>"; // Debug
        require_once __DIR__ . '/' . $fichierVue;
        ?>
    </main>

</body>

</html>