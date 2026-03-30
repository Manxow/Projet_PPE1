<div class="carte-action w-p">
    <div class="form-container">
        <div class="form">
            <h1 class="titre-centre">Profil de <?php echo htmlspecialchars($monJoueur->getNomUtilisateur()); ?></h1>

            <?php if (isset($_SESSION['flash_message_succes'])): ?>
                <div class="alerte alerte-succes">
                    <?= htmlspecialchars($_SESSION['flash_message_succes']) ?>
                </div>
                <?php unset($_SESSION['flash_message_succes']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_message_erreur'])): ?>
                <div class="alerte alerte-erreur">
                    <?= htmlspecialchars($_SESSION['flash_message_erreur']) ?>
                </div>
                <?php unset($_SESSION['flash_message_erreur']); ?>
            <?php endif; ?>

            <form action="index.php?action=traiter_profile" method="POST">

                <div class="form-groupe">
                    <label for="user">Nom d'utilisateur :</label>
                    <input type="text" id="user" name="user" value="<?= htmlspecialchars($monJoueur->getNomUtilisateur()); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="pw">Mot de passe actuel :</label>
                    <input type="password" id="pw" name="pw">
                </div>

                <div class="form-groupe form-groupe-haut">
                    <label for="new_pw">Nouveau mot de passe :</label>
                    <div class="input-et-regles">
                        <input type="password" id="new_pw" name="new_pw">
                        <?php
                        $erreursMdp = $_SESSION['erreurs_mdp'] ?? null;
                        $aTente = isset($_SESSION['erreurs_mdp']);
                        unset($_SESSION['erreurs_mdp']);
                        ?>
                        <div class="bloc-regles-mdp">
                            <div><?= $aTente ? ($erreursMdp['longueur'] ? '✅' : '❌') : '🔸' ?> <span class="<?= $aTente && $erreursMdp['longueur'] ? 'valide' : '' ?>">8 caractères minimum</span></div>
                            <div><?= $aTente ? ($erreursMdp['majuscule'] ? '✅' : '❌') : '🔸' ?> <span class="<?= $aTente && $erreursMdp['majuscule'] ? 'valide' : '' ?>">Au moins une majuscule</span></div>
                            <div><?= $aTente ? ($erreursMdp['minuscule'] ? '✅' : '❌') : '🔸' ?> <span class="<?= $aTente && $erreursMdp['minuscule'] ? 'valide' : '' ?>">Au moins une minuscule</span></div>
                            <div><?= $aTente ? ($erreursMdp['chiffre'] ? '✅' : '❌') : '🔸' ?> <span class="<?= $aTente && $erreursMdp['chiffre'] ? 'valide' : '' ?>">Au moins un chiffre</span></div>
                            <div><?= $aTente ? ($erreursMdp['special'] ? '✅' : '❌') : '🔸' ?> <span class="<?= $aTente && $erreursMdp['special'] ? 'valide' : '' ?>">Au moins un caractère spécial (!@#$%)</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-groupe">
                    <label for="confirm_pw">Vérifier nouveau mot de passe :</label>
                    <input type="password" id="confirm_pw" name="confirm_pw">
                </div>

                <div class="form-groupe">
                    <label for="nom">Nom : </label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($monJoueur->getNom()); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="prenom">Prénom : </label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($monJoueur->getPrenom()); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="poste">Poste : </label>
                    <div class="radio-options">
                        <input type="radio" id="poste_terrain" name="poste" value="Terrain" <?= ($monJoueur->getPoste() == "Terrain") ? 'checked' : '' ?>>
                        <label for="poste_terrain">Joueur de terrain</label>
                        <input type="radio" id="poste_gardien" name="poste" value="Gardien" <?= ($monJoueur->getPoste() == "Gardien") ? 'checked' : '' ?> required>
                        <label for="poste_gardien">Gardien</label>
                    </div>
                </div>

                <div class="form-groupe">
                    <label for="lvl">Niveau : </label>
                    <div class="radio-options">
                        <input type="radio" id="lvl_debutant" name="lvl" value="Debutant" <?= ($monJoueur->getNiveau() == "Debutant") ? 'checked' : '' ?>>
                        <label for="lvl_debutant">Débutant</label>
                        <input type="radio" id="lvl_confirme" name="lvl" value="Confirme" <?= ($monJoueur->getNiveau() == "Confirme") ? 'checked' : '' ?>>
                        <label for="lvl_confirme">Confirmé</label>
                        <input type="radio" id="lvl_expert" name="lvl" value="Expert" <?= ($monJoueur->getNiveau() == "Expert") ? 'checked' : '' ?> required>
                        <label for="lvl_expert">Expert</label>
                    </div>
                </div>

                <div class="form-groupe">
                    <label for="team">Équipe : </label>
                    <select name="team" id="team" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($listeEquipes as $equipe): ?>
                            <option value="<?= $equipe['id_equipe'] ?>" <?php if ($monJoueur->getEquipe() == $equipe['id_equipe']) echo 'selected'; ?>>
                                <?= htmlspecialchars($equipe['nom']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>

                    <?php if (!empty($nomTeam) && $nomTeam !== 'Aucune'): ?>
                        <button type="submit" form="form-quitter" class="btn btn-rouge ml-10" title="Quitter l'équipe actuelle">Quitter</button>
                    <?php endif; ?>
                </div>

                <div class="form-groupe-vertical">
                    <label for="code_equipe">Code d'accès (uniquement si tu changes d'équipe) :</label>
                    <input type="text" id="code_equipe" name="code_equipe" placeholder="Code secret de la nouvelle équipe">
                </div>

                <div class="form-actions">
                    <input type="submit" value="Modifier" class="btn">
                </div>
            </form>
        </div>

        <?php if (!empty($nomTeam) && $nomTeam !== 'Aucune'): ?>
            <form id="form-quitter" action="index.php?action=quitter_equipe" method="POST" onsubmit="return confirm('Es-tu sûr de vouloir quitter l\'équipe \'<?= addslashes(htmlspecialchars($nomTeam)) ?>\' ?');">
            </form>
        <?php endif; ?>
    </div>
</div>