<div class="carte-action w-p">
    <h2 class="titre-centre titre-sans-marge-haut">Rejoindre le centre Five M2L</h2>

    <?php
    require_once '../models/ModelEquipe.php';
    $listeEquipes = ModelEquipe::getAllEquipes();
    ?>

    <p class="sous-titre-centre">Crée ton profil joueur pour inscrire ton équipe aux prochains tournois.</p>

    <form action="index.php?action=traiter_inscription_joueur" method="POST">
        <?php
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        if (isset($_SESSION['flash_message_erreur'])): ?>
            <div class="alerte alerte-erreur">
                <?= htmlspecialchars($_SESSION['flash_message_erreur']) ?>
            </div>
            <?php unset($_SESSION['flash_message_erreur']); ?>
        <?php endif; ?>

        <div class="form-groupe-vertical">
            <label for="user">Nom d'utilisateur :</label>
            <input type="text" id="user" name="user" value="<?= htmlspecialchars($formData['user'] ?? '') ?>" required>
        </div>

        <div class="form-groupe-vertical">
            <label for="pw">Mot de passe :</label>
            <div class="input-et-regles">
                <input type="password" id="pw" name="pw" required>
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

        <div class="form-groupe-vertical">
            <label for="confirm_pw">Confirmer le mot de passe :</label>
            <input type="password" id="confirm_pw" name="confirm_pw" required>
        </div>

        <div class="form-groupe-vertical">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($formData['nom'] ?? '') ?>" required>
        </div>

        <div class="form-groupe-vertical">
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($formData['prenom'] ?? '') ?>" required>
        </div>

        <div class="form-groupe-vertical">
            <label>Poste :</label>
            <div class="radio-options">
                <label><input type="radio" name="poste" value="Terrain" <?= (isset($formData['poste']) && $formData['poste'] === 'Terrain') ? 'checked' : '' ?> required> Joueur de terrain</label>
                <label><input type="radio" name="poste" value="Gardien" <?= (isset($formData['poste']) && $formData['poste'] === 'Gardien') ? 'checked' : '' ?> required> Gardien</label>
            </div>
        </div>

        <div class="form-groupe-vertical">
            <label>Niveau :</label>
            <div class="radio-options">
                <label><input type="radio" name="lvl" value="Debutant" <?= (isset($formData['lvl']) && $formData['lvl'] === 'Debutant') ? 'checked' : '' ?> required> Débutant</label>
                <label><input type="radio" name="lvl" value="Confirme" <?= (isset($formData['lvl']) && $formData['lvl'] === 'Confirme') ? 'checked' : '' ?> required> Confirmé</label>
                <label><input type="radio" name="lvl" value="Expert" <?= (isset($formData['lvl']) && $formData['lvl'] === 'Expert') ? 'checked' : '' ?> required> Expert</label>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" value="S'inscrire" class="btn btn-primaire btn-large">
        </div>

        <div class="lien-connexion">
            Déjà inscrit ? <br> <a href="index.php?action=connexion">Va dans le vestiaire (Connexion)</a>
        </div>
    </form>
</div>