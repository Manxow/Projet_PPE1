<div class="carte-action w-p">
    <h2 class="titre-centre titre-sans-marge-haut">Connexion au vestiaire</h2>

    <form action="index.php?action=traiter_connexion" method="POST">
        <?php if (isset($_SESSION['flash_message_erreur'])): ?>
            <div class="alerte alerte-erreur">
                <?= htmlspecialchars($_SESSION['flash_message_erreur']) ?>
            </div>
            <?php unset($_SESSION['flash_message_erreur']); ?>
        <?php endif; ?>

        <div class="form-groupe-vertical">
            <label for="user">Login du joueur :</label>
            <input type="text" id="user" name="user"
                value="<?= isset($_SESSION['nom_utilisateur']) ? htmlspecialchars($_SESSION['nom_utilisateur']) : '' ?>"
                required>
        </div>

        <div class="form-groupe-vertical">
            <label for="pw">Mot de passe :</label>
            <input type="password" id="pw" name="pw" required>
        </div>

        <div class="form-actions">
            <input type="submit" value="Entrer sur le terrain" class="btn btn-primaire btn-large">
        </div>

        <div class="lien-connexion">
            Pas encore inscrit ? <br> <a href="index.php?action=inscription">Inscris-toi dans le vestiaire</a>
        </div>
    </form>
</div>