<?php if (isset($_SESSION['flash_message_succes'])): ?>
    <div class="alerte alerte-succes"><?= htmlspecialchars($_SESSION['flash_message_succes']) ?></div>
    <?php unset($_SESSION['flash_message_succes']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_message_erreur'])): ?>
    <div class="alerte alerte-erreur"><?= htmlspecialchars($_SESSION['flash_message_erreur']) ?></div>
    <?php unset($_SESSION['flash_message_erreur']); ?>
<?php endif; ?>

<?php
$editEquipeId = $_GET['edit_equipe_id'] ?? null;
$estAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<?php if (!$estConnecte): ?>
    <div class="carte-action w-g">
        <div class="hub-equipe-entete">
            <h1 class="titre-sans-marge-haut">Les Équipes du Tournoi</h1>
            <p>Tu dois être connecté pour rejoindre une équipe ou fonder la tienne !</p>
            <div class="actions-visiteur">
                <a href="index.php?action=connexion" class="btn">Se connecter</a>
                <a href="index.php?action=inscription" class="btn btn-orange">Créer un compte</a>
            </div>
        </div>
    </div>

    <div class="liste-equipes-publiques mt-40">
        <div class="carte-action w-g">
            <h2 class="titre-centre titre-sans-marge-haut">Équipes déjà inscrites :</h2>
            <?php if (!empty($listeEquipesValidees)): ?>
                <div class="liste-equipes-complete">
                    <?php foreach ($listeEquipesValidees as $equipe): ?>
                        <div class="carte-equipe-detail">
                            <h3><?= htmlspecialchars($equipe['nom']) ?></h3>

                            <?php if (!empty($equipe['joueurs'])): ?>
                                <table class="tableau-joueurs">
                                    <thead>
                                        <tr>
                                            <th>Pseudo</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Poste</th>
                                            <th>Niveau</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($equipe['joueurs'] as $joueur): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($joueur['user']) ?></td>
                                                <td><?= htmlspecialchars($joueur['nom']) ?></td>
                                                <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                                                <td><?= htmlspecialchars($joueur['poste']) ?></td>
                                                <td><?= htmlspecialchars($joueur['niveau']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <p class="info-equipe">Total : <strong><?= count($equipe['joueurs']) ?></strong> joueur(s)</p>
                            <?php else: ?>
                                <p class="titre-centre">Aucun joueur inscrit dans cette équipe.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="titre-centre">Aucune équipe n'est encore validée.</p>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($aUneEquipe): ?>

    <div class="carte-action w-g">
        <h1 class="titre-centre titre-sans-marge-haut">Mon Équipe : <?= htmlspecialchars($nomDeMonEquipe ?? 'Inconnu') ?></h1>

        <?php if (isset($statutEquipe) && $statutEquipe === 'en_attente'): ?>
            <div class="alerte alerte-orange">
                ⏳ Ton équipe est actuellement en attente de validation par l'administrateur.
            </div>

            <?php if (isset($idCapitaine) && $_SESSION['id_joueur'] == $idCapitaine): ?>
                <form action="index.php?action=annuler_equipe" method="POST" onsubmit="return confirm('Es-tu sûr de vouloir annuler ta demande et dissoudre l\'équipe ?');" class="form-annulation">
                    <button type="submit" class="btn btn-rouge">Annuler ma demande</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <p class="titre-centre">Bienvenue dans le vestiaire ! Ici tu retrouveras tes coéquipiers et ceux des autres équipes.</p>
        <?php endif; ?>

        <div class="sous-navigation">
            <a href="index.php?action=equipe&sous_onglet=mes_joueurs" class="sous-onglet <?= $sous_onglet == 'mes_joueurs' ? 'actif' : '' ?>">Mon équipe</a>
            <a href="index.php?action=equipe&sous_onglet=toutes_equipes" class="sous-onglet onglet-bleu <?= $sous_onglet == 'toutes_equipes' ? 'actif' : '' ?>">Les équipes</a>
        </div>
    </div>

    <div class="contenu-sous-onglet">

        <?php if ($sous_onglet == 'mes_joueurs'): ?>
            <div class="carte-action w-g">
                <h2 class="titre-centre">Joueurs de <?= htmlspecialchars($nomDeMonEquipe ?? 'l\'équipe') ?></h2>

                <?php if (!empty($joueursMonEquipe)): ?>
                    <table class="tableau-joueurs">
                        <thead>
                            <tr>
                                <th>Pseudo</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Poste</th>
                                <th>Niveau</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($joueursMonEquipe as $joueur): ?>
                                <tr>
                                    <td><?= htmlspecialchars($joueur['user']) ?></td>
                                    <td><?= htmlspecialchars($joueur['nom']) ?></td>
                                    <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                                    <td><?= htmlspecialchars($joueur['poste']) ?></td>
                                    <td><?= htmlspecialchars($joueur['niveau']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="info-equipe">Total : <strong><?= count($joueursMonEquipe) ?></strong> joueur(s)</p>
                <?php else: ?>
                    <p class="titre-centre">Aucun joueur dans ton équipe pour le moment.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($sous_onglet == 'toutes_equipes'): ?>
            <div class="carte-action w-g">
                <h2 class="titre-centre">Toutes les équipes</h2>

                <?php if (!empty($listeEquipesValidees)): ?>
                    <div class="liste-equipes-complete">
                        <?php foreach ($listeEquipesValidees as $equipe): ?>
                            <div class="carte-equipe-detail">
                                <h3><?= htmlspecialchars($equipe['nom']) ?></h3>

                                <?php if (!empty($equipe['joueurs'])): ?>
                                    <table class="tableau-joueurs">
                                        <thead>
                                            <tr>
                                                <th>Pseudo</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Poste</th>
                                                <th>Niveau</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($equipe['joueurs'] as $joueur): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($joueur['user']) ?></td>
                                                    <td><?= htmlspecialchars($joueur['nom']) ?></td>
                                                    <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                                                    <td><?= htmlspecialchars($joueur['poste']) ?></td>
                                                    <td><?= htmlspecialchars($joueur['niveau']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <p class="info-equipe">Total : <strong><?= count($equipe['joueurs']) ?></strong> joueur(s)</p>
                                <?php else: ?>
                                    <p class="titre-centre">Aucun joueur inscrit dans cette équipe.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="titre-centre">Aucune équipe validée pour le moment.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

<?php else: ?>

    <div class="carte-action w-g">
        <div class="hub-equipe-entete">
            <h1 class="titre-sans-marge-haut">Gestion d'Équipe</h1>
        </div>

        <div class="sous-navigation">
            <a href="index.php?action=equipe&sous_onglet=liste" class="sous-onglet <?= $sous_onglet == 'liste' ? 'actif' : '' ?>">Les équipes</a>
            <a href="index.php?action=equipe&sous_onglet=rejoindre" class="sous-onglet onglet-vert <?= $sous_onglet == 'rejoindre' ? 'actif' : '' ?>">Rejoindre une équipe</a>
            <a href="index.php?action=equipe&sous_onglet=creer" class="sous-onglet onglet-orange <?= $sous_onglet == 'creer' ? 'actif' : '' ?>">Créer une équipe</a>
        </div>
    </div>

    <div class="contenu-sous-onglet">

        <?php if ($sous_onglet == 'liste'): ?>
            <div class="liste-equipes-publiques">
                <h2>Toutes les équipes inscrites :</h2>
                <?php if (!empty($listeEquipesValidees)): ?>
                    <div class="liste-equipes-complete">
                        <?php foreach ($listeEquipesValidees as $equipe): ?>
                            <?php $enModeEditionEquipe = ($editEquipeId == $equipe['id_equipe'] && $estAdmin); ?>

                            <?php if ($enModeEditionEquipe): ?>
                                <div class="carte-equipe-detail mode-edition-equipe">
                                    <h3>Modifier l'équipe</h3>
                                    <form action="index.php?action=traiter_editer_equipe" method="POST" class="form-edition-inline">
                                        <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                        <input type="hidden" name="provenance" value="equipe">
                                        <input type="text" name="nom_equipe" value="<?= htmlspecialchars($equipe['nom']) ?>" required class="input-inline">

                                        <div class="actions-inline">
                                            <button type="submit" class="btn btn-primaire btn-petit" title="Enregistrer">💾</button>
                                            <a href="index.php?action=equipe&sous_onglet=liste" class="btn btn-rouge btn-petit" title="Annuler">❌</a>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="carte-equipe-detail">
                                    <div class="ligne-equipe-admin">
                                        <h3><?= htmlspecialchars($equipe['nom']) ?></h3>

                                        <?php if ($estAdmin): ?>
                                            <div class="actions-admin-equipe">
                                                <a href="index.php?action=equipe&sous_onglet=liste&edit_equipe_id=<?= $equipe['id_equipe'] ?>" class="btn btn-orange btn-petit" title="Modifier">✏️</a>

                                                <form action="index.php?action=admin_supprimer_equipe" method="POST" class="form-sans-marge" onsubmit="return confirm('Supprimer définitivement cette équipe et libérer ses joueurs ?');">
                                                    <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
                                                    <input type="hidden" name="provenance" value="equipe">
                                                    <button type="submit" class="btn btn-rouge btn-petit" title="Supprimer">🗑️</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($equipe['joueurs'])): ?>
                                        <table class="tableau-joueurs">
                                            <thead>
                                                <tr>
                                                    <th>Pseudo</th>
                                                    <th>Nom</th>
                                                    <th>Prénom</th>
                                                    <th>Poste</th>
                                                    <th>Niveau</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($equipe['joueurs'] as $joueur): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($joueur['user']) ?></td>
                                                        <td><?= htmlspecialchars($joueur['nom']) ?></td>
                                                        <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                                                        <td><?= htmlspecialchars($joueur['poste']) ?></td>
                                                        <td><?= htmlspecialchars($joueur['niveau']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <p class="info-equipe">Total : <strong><?= count($equipe['joueurs']) ?></strong> joueur(s)</p>
                                    <?php else: ?>
                                        <p class="titre-centre">Aucun joueur inscrit dans cette équipe.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucune équipe n'est encore validée. Sois le premier à en créer une !</p>
                <?php endif; ?>
            </div>

        <?php elseif ($sous_onglet == 'rejoindre'): ?>
            <div class="carte-action carte-rejoindre mx-auto carte-connexion">
                <h2 class="titre-centre titre-sans-marge-haut">Rejoindre une équipe</h2>
                <form action="index.php?action=rejoindre_equipe" method="POST">
                    <div class="form-groupe-vertical">
                        <label for="id_equipe">Sélectionne l'équipe :</label>
                        <select name="id_equipe" id="id_equipe" required>
                            <option value="">-- Choisis une équipe --</option>
                            <?php if (!empty($listeEquipesValidees)): ?>
                                <?php foreach ($listeEquipesValidees as $equipe): ?>
                                    <option value="<?= $equipe['id_equipe'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-groupe-vertical">
                        <label for="code_acces">Code d'accès secret :</label>
                        <input type="text" id="code_acces" name="code_acces" required placeholder="Saisi le code donné par le capitaine">
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="Intégrer l'équipe" class="btn btn-primaire btn-large">
                    </div>
                </form>
            </div>

        <?php elseif ($sous_onglet == 'creer'): ?>
            <div class="carte-action carte-creer mx-auto carte-connexion">
                <h2 class="titre-centre titre-sans-marge-haut">Fonder une équipe</h2>
                <form action="index.php?action=creer_equipe" method="POST">
                    <div class="form-groupe-vertical">
                        <label for="nom_equipe">Nom de la nouvelle équipe :</label>
                        <input type="text" id="nom_equipe" name="nom_equipe" required placeholder="Ex: Les Invincibles">
                    </div>
                    <div class="form-groupe-vertical">
                        <label for="nouveau_code">Invente un code d'accès :</label>
                        <input type="text" id="nouveau_code" name="nouveau_code" required placeholder="Ex: M2L2024">
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="Demander la création" class="btn btn-orange btn-large">
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>