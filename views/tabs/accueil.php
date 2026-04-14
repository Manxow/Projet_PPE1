<div class="carte-action w-g">
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

    <p class="titre-centre">Vue rapide de ton activité tournoi et de tes matchs.</p>

    <div class="accueil-dashboard">
        <section class="accueil-colonne">
            <h2 class="accueil-colonne-titre">🏆 Actus Tournoi</h2>

            <div class="accueil-bloc">
                <h3>Mon tournoi en cours</h3>

                <?php if ($estConnecte && $aUneEquipe && !empty($monTournoiEnCours)): ?>
                    <a class="bloc-tournoi-clicable" href="index.php?action=tournoi#tournoi-<?= (int)$monTournoiEnCours['id_tournoi'] ?>">
                        <p class="accueil-ligne-titre"><?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $monTournoiEnCours['nom'])) ?></p>
                        <p>📅 Du <?= date('d/m/Y', strtotime($monTournoiEnCours['date_debut'])) ?> au <?= date('d/m/Y', strtotime($monTournoiEnCours['date_fin'])) ?></p>
                        <p>Inscrits : <strong><?= (int)$monTournoiEnCours['nb_inscrits'] ?> / 16</strong></p>
                        <p class="accueil-indice-lien">Voir ce tournoi</p>
                    </a>
                <?php else: ?>
                    <p class="texte-centre">Aucun tournoi en cours pour ton équipe.</p>
                <?php endif; ?>
            </div>

            <div class="accueil-bloc mt-10">
                <h3>3 prochains tournois</h3>
                <?php if (!empty($prochainsTournois)): ?>
                    <div class="accueil-liste-tournois">
                        <?php foreach ($prochainsTournois as $tournoi): ?>
                            <a class="accueil-tournoi-card accueil-tournoi-lien" href="index.php?action=tournoi#tournoi-<?= (int)$tournoi['id_tournoi'] ?>">
                                <p class="accueil-ligne-titre"><?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $tournoi['nom'])) ?></p>
                                <p>📅 Du <?= date('d/m/Y', strtotime($tournoi['date_debut'])) ?> au <?= date('d/m/Y', strtotime($tournoi['date_fin'])) ?></p>
                                <p>Statut : <?= htmlspecialchars($tournoi['statut']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="texte-centre">Aucun prochain tournoi.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="accueil-colonne">
            <h2 class="accueil-colonne-titre">⚽ Actus Matchs</h2>

            <div class="accueil-bloc">
                <h3>3 prochains matchs de mon équipe</h3>
                <?php if ($estConnecte && $aUneEquipe && !empty($prochainsMatchs)): ?>
                    <div class="accueil-liste-matchs">
                        <?php foreach ($prochainsMatchs as $match): ?>
                            <article class="accueil-match-card a-venir">
                                <p class="accueil-match-meta">
                                    <?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $match['nom_tournoi'])) ?>
                                    <span>• <?= htmlspecialchars($match['phase_affichage']) ?></span>
                                </p>
                                <p class="accueil-match-equipes"><?= htmlspecialchars($match['nom_equipe1']) ?> vs <?= htmlspecialchars($match['nom_equipe2']) ?></p>
                                <p class="accueil-match-infos">
                                    <span><?= $match['date_match'] ? date('d/m/Y', strtotime($match['date_match'])) : 'Date non planifiée' ?></span>
                                    <strong>À venir</strong>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="texte-centre">Aucun prochain match à afficher.</p>
                <?php endif; ?>
            </div>

            <div class="accueil-bloc mt-10">
                <h3>3 derniers matchs de mon équipe</h3>
                <?php if ($estConnecte && $aUneEquipe && !empty($derniersMatchs)): ?>
                    <div class="accueil-liste-matchs">
                        <?php foreach ($derniersMatchs as $match): ?>
                            <article class="accueil-match-card termine">
                                <p class="accueil-match-meta">
                                    <?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $match['nom_tournoi'])) ?>
                                    <span>• <?= htmlspecialchars($match['phase_affichage']) ?></span>
                                </p>
                                <p class="accueil-match-equipes"><?= htmlspecialchars($match['nom_equipe1']) ?> vs <?= htmlspecialchars($match['nom_equipe2']) ?></p>
                                <p class="accueil-match-infos">
                                    <span><?= $match['date_match'] ? date('d/m/Y', strtotime($match['date_match'])) : 'Date non planifiée' ?></span>
                                    <strong><?= (int)$match['buts_equipe1_final'] ?> - <?= (int)$match['buts_equipe2_final'] ?></strong>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="texte-centre">Aucun match terminé à afficher.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>