<div class="carte-action w-m">
    <h1 class="titre-centre titre-sans-marge-haut">Bienvenue au centre Five M2L !</h1>

    <?php if ($estConnecte && !$aUneEquipe): ?>
        <div class="encart-action-accueil">
            <h2 class="titre-sans-marge-haut" style="color: #2e7d32; margin-bottom: 15px;">Prêt à entrer sur le terrain ?</h2>
            <p>Tu n'as pas encore d'équipe. Rejoins tes amis ou fonde ton propre club pour participer aux tournois !</p>

            <div class="actions-accueil">
                <a href="index.php?action=equipe&sous_onglet=rejoindre" class="btn">Rejoindre une équipe</a>
                <a href="index.php?action=equipe&sous_onglet=creer" class="btn">Créer une équipe</a>
            </div>
        </div>
    <?php endif; ?>

    <p class="titre-centre">Découvrez nos prochains tournois et l'actualité du centre...</p>
</div>