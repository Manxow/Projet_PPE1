<div class="carte-action carte-navigation w-m">
    <div class="hub-equipe-entete">
        <h1 class="titre-sans-marge-haut">👑 Espace d'Administration</h1>
        <p>Gère les inscriptions des équipes pour le tournoi.</p>
    </div>

    <div class="sous-navigation">
        <a href="index.php?action=admin_panel&sous_onglet=attente" class="sous-onglet <?= $sous_onglet == 'attente' ? 'onglet-orange actif' : '' ?>">
            Demandes en attente
            <?php if (count($equipesEnAttente) > 0): ?>
                <span class="badge-notification"><?= count($equipesEnAttente) ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?action=admin_panel&sous_onglet=validees" class="sous-onglet <?= $sous_onglet == 'validees' ? 'onglet-vert actif' : '' ?>">
            Équipes validées (<?= count($equipesValidees) ?>)
        </a>

        <a href="index.php?action=admin_panel&sous_onglet=tournois" class="sous-onglet <?= $sous_onglet == 'tournois' ? 'actif' : '' ?>">
            🏆 Gérer Tournois
            <?php
            $nbTournoisPleins = ModelTournoi::getNbTournoisComplets();
            if ($nbTournoisPleins > 0):
            ?>
                <span class="badge-notification"><?= $nbTournoisPleins ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message_succes'])): ?>
    <div class="alerte alerte-succes"><?= htmlspecialchars($_SESSION['flash_message_succes']) ?></div>
    <?php unset($_SESSION['flash_message_succes']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_message_erreur'])): ?>
    <div class="alerte alerte-erreur"><?= htmlspecialchars($_SESSION['flash_message_erreur']) ?></div>
    <?php unset($_SESSION['flash_message_erreur']); ?>
<?php endif; ?>

<div class="contenu-sous-onglet">

    <?php if ($sous_onglet == 'attente'): ?>
        <div class="liste-equipes-publiques w-m">
            <h2 class="titre-centre titre-sans-marge-haut">Équipes en attente de validation</h2>
            <?php if (!empty($equipesEnAttente)): ?>
                <ul>
                    <?php foreach ($equipesEnAttente as $equipe): ?>
                        <li class="ligne-demande">
                            <span>
                                <strong><?= htmlspecialchars($equipe['nom']) ?></strong>
                                <span style="color: #666; font-size: 0.9em;">(Code : <?= htmlspecialchars($equipe['code_acces']) ?>)</span>
                            </span>
                            <div class="groupe-boutons">
                                <form action="index.php?action=accepter_equipe" method="POST" class="form-sans-marge">
                                    <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                    <button type="submit" class="btn btn-vert-admin">Accepter</button>
                                </form>
                                <form action="index.php?action=refuser_equipe" method="POST" class="form-sans-marge" onsubmit="return confirm('Es-tu sûr ?');">
                                    <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                    <button type="submit" class="btn btn-rouge">Refuser</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="titre-centre">Aucune demande d'équipe en attente pour le moment.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($sous_onglet == 'validees'): ?>
        <div class="liste-equipes-publiques w-m">
            <h2 class="titre-centre titre-sans-marge-haut">Équipes officiellement inscrites</h2>
            <?php if (!empty($equipesValidees)): ?>
                <ul>
                    <?php foreach ($equipesValidees as $equipe): ?>
                        <?php $enModeEditionEquipe = (isset($_GET['edit_equipe_id']) && $_GET['edit_equipe_id'] == $equipe['id_equipe']); ?>
                        <?php if ($enModeEditionEquipe): ?>
                            <li class="ligne-equipe-admin mode-edition-equipe">
                                <form action="index.php?action=traiter_editer_equipe" method="POST" class="form-edition-inline">
                                    <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                    <input type="hidden" name="provenance" value="admin_panel">
                                    <input type="text" name="nom_equipe" value="<?= htmlspecialchars($equipe['nom']) ?>" required class="input-inline">
                                    <div class="actions-inline">
                                        <button type="submit" class="btn btn-primaire btn-petit">💾</button>
                                        <a href="index.php?action=admin_panel&sous_onglet=validees" class="btn btn-rouge btn-petit">❌</a>
                                    </div>
                                </form>
                            </li>
                        <?php else: ?>
                            <li class="ligne-equipe-admin">
                                <span><strong><?= htmlspecialchars($equipe['nom']) ?></strong></span>
                                <div class="actions-admin-equipe">
                                    <a href="index.php?action=admin_panel&sous_onglet=validees&edit_equipe_id=<?= $equipe['id_equipe'] ?>" class="btn btn-orange btn-petit">✏️</a>
                                    <form action="index.php?action=admin_supprimer_equipe" method="POST" class="form-sans-marge" onsubmit="return confirm('Supprimer définitivement cette équipe ?');">
                                        <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                        <button type="submit" class="btn btn-rouge btn-petit">🗑️</button>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="titre-centre">Aucune équipe n'est validée.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($sous_onglet == 'tournois'): ?>
        <div class="carte-action carte-creer w-m">
            <h2 class="titre-centre titre-sans-marge-haut">🏆 Créer un Tournoi</h2>
            <form action="index.php?action=admin_creer_tournoi" method="POST">
                <div class="form-groupe-vertical">
                    <label>Nom du tournoi</label>
                    <input type="text" name="nom_tournoi" placeholder="Ex: Summer Cup 2026" required>
                </div>
                <div class="form-groupe-vertical">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" required>
                </div>
                <div class="form-groupe-vertical">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-vert-admin btn-large">Lancer les inscriptions</button>
                </div>
            </form>
        </div>

        <div class="liste-equipes-publiques w-m mt-40">
            <h2 class="titre-centre titre-sans-marge-haut">Tournois enregistrés</h2>
            <?php if (!empty($listeTournois)): ?>
                <ul>
                    <?php foreach ($listeTournois as $tournoi): ?>
                        <li class="ligne-liste-tournoi">
                            <div class="info-tournoi-admin">
                                <strong><?= htmlspecialchars($tournoi['nom']) ?></strong>
                                <span class="dates-admin"> (Du <?= date('d/m', strtotime($tournoi['date_debut'])) ?> au <?= date('d/m/y', strtotime($tournoi['date_fin'])) ?>)</span>
                            </div>
                            <div class="stats-tournoi-admin">
                                <span class="info-bulle"><?= $tournoi['nb_inscrits'] ?>/16 inscrits</span>
                                <span class="badge-statut statut-<?= $tournoi['statut'] ?>"><?= strtoupper($tournoi['statut']) ?></span>
                                <form action="index.php?action=admin_supprimer_tournoi" method="POST" class="form-sans-marge">
                                    <input type="hidden" name="id_tournoi" value="<?= $tournoi['id_tournoi'] ?>">
                                    <button type="submit" class="btn btn-rouge btn-action-mini">🗑️</button>
                                </form>
                            </div>
                            <?php if ($tournoi['statut'] === 'complet'): ?>
                                <div class="encart-action-admin mt-10">
                                    <p class="texte-action-admin">⚠️ Le tournoi est complet !</p>
                                    <form action="index.php?action=admin_generer_poules" method="POST" class="form-sans-marge">
                                        <input type="hidden" name="id_tournoi" value="<?= $tournoi['id_tournoi'] ?>">
                                        <button type="submit" class="btn btn-primaire btn-large">🎲 Lancer le tirage</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="titre-centre">Aucun tournoi créé.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>