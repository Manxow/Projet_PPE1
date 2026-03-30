<div class="carte-action w-g">
    <div class="hub-equipe-entete">
        <h1 class="titre-sans-marge-haut">🏆 Tournois & Compétitions</h1>
        <p>Rejoignez un tournoi pour affronter les meilleures équipes de la M2L.</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_message_succes'])): ?>
    <div class="alerte alerte-succes w-m"><?= htmlspecialchars($_SESSION['flash_message_succes']) ?></div>
    <?php unset($_SESSION['flash_message_succes']); ?>
<?php endif; ?>

<div class="carte-action w-g">
    <?php if (!empty($tournois)): ?>
        <?php foreach ($tournois as $t): ?>
            <?php
            $enModeEdition = (isset($_GET['edit_id']) && $_GET['edit_id'] == $t['id_tournoi'] && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
            // On détermine la classe de couleur de bordure si besoin
            $classeStatut = ($t['nb_inscrits'] < 16) ? 'carte-rejoindre' : '';
            ?>

            <?php if ($enModeEdition): ?>
                <div class="carte-action carte-edition w-m">
                    <form action="index.php?action=traiter_editer_tournoi" method="POST" class="form-sans-marge">
                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">

                        <div class="form-groupe-vertical">
                            <label>Nom du tournoi :</label>
                            <input type="text" name="nom_tournoi" value="<?= htmlspecialchars($t['nom']) ?>" required>
                        </div>

                        <div class="form-groupe-horizontal">
                            <div class="form-groupe-vertical flex-1">
                                <label>Date de début :</label>
                                <input type="date" name="date_debut" value="<?= htmlspecialchars($t['date_debut']) ?>" required>
                            </div>
                            <div class="form-groupe-vertical flex-1">
                                <label>Date de fin :</label>
                                <input type="date" name="date_fin" value="<?= htmlspecialchars($t['date_fin']) ?>" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primaire btn-petit">💾 Enregistrer</button>
                            <a href="index.php?action=tournoi" class="btn btn-rouge btn-petit ml-10">❌ Annuler</a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="carte-action <?= $classeStatut ?> w-m">
                    <div class="ligne-tournoi">
                        <div class="info-tournoi">
                            <h2 class="titre-sans-marge-haut"><?= htmlspecialchars($t['nom']) ?></h2>
                            <p class="date-tournoi">📅 Du <?= date('d/m', strtotime($t['date_debut'])) ?> au <?= date('d/m/Y', strtotime($t['date_fin'])) ?></p>
                            <p class="places-tournoi">Inscrits : <strong><?= $t['nb_inscrits'] ?> / 16</strong></p>
                        </div>

                        <div class="actions-tournoi">
                            <?php if ($estCapitaine): ?>
                                <?php if (ModelTournoi::estDejaInscrit($_SESSION['idTeam'], $t['id_tournoi'])): ?>
                                    <span class="badge-inscrit">Inscrit ✅</span>
                                <?php elseif ($t['nb_inscrits'] < 16): ?>
                                    <form action="index.php?action=inscription_tournoi" method="POST" class="form-sans-marge">
                                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                        <button type="submit" class="btn btn-vert-admin">Inscrire l'équipe</button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn btn-rouge btn-desactive">Complet</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <div class="actions-admin-equipe">
                                    <a href="index.php?action=tournoi&edit_id=<?= $t['id_tournoi'] ?>" class="btn btn-orange btn-petit" title="Modifier">✏️</a>
                                    <form action="index.php?action=admin_supprimer_tournoi" method="POST" class="form-sans-marge"
                                        onsubmit="return confirm('Supprimer définitivement ce tournoi ?');">
                                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                        <button type="submit" class="btn btn-rouge btn-petit" title="Supprimer">🗑️</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && $t['statut'] === 'complet'): ?>
                        <div class="encart-action-admin mt-10">
                            <p class="texte-action-admin">⚠️ Le tournoi est complet !</p>
                            <form action="index.php?action=admin_generer_poules" method="POST" class="form-sans-marge">
                                <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                <button type="submit" class="btn btn-primaire btn-large" onclick="return confirm('Lancer le tirage ?');">
                                    🎲 Lancer le tirage
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="carte-action w-m">
            <p class="titre-centre">Aucun tournoi n'est ouvert pour le moment.</p>
        </div>
    <?php endif; ?>
</div>