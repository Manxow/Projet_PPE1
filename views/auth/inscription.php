<h2>Rejoindre le centre Five M2L</h2>

<?php
// Note : Idéalement, dans une architecture MVC stricte, 
// ces lignes devraient être dans le Contrôleur, pas dans la Vue !
require_once '../models/ModelEquipe1.php';
$listeEquipes = ModelEquipe::getAllEquipes();
?>

<div class="form-container">
    <p>Crée ton profil joueur pour inscrire ton équipe aux prochains tournois.</p>

    <form action="index.php?action=traiter_inscription" method="POST">

        <div class="form-groupe">
            <label for="user">Nom d'utilisateur :</label>
            <input type="text" id="user" name="user" required>
        </div>

        <div class="form-groupe">
            <label for="pw">Mot de passe :</label>
            <input type="password" id="pw" name="pw" required>
        </div>

        <div class="form-groupe">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="form-groupe">
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>

        <div class="form-groupe">
            <label>Poste :</label>
            <div class="radio-options">
                <label><input type="radio" name="poste" value="Terrain" required> Joueur de terrain</label>
                <label><input type="radio" name="poste" value="Gardien" required> Gardien</label>
            </div>
        </div>

        <div class="form-groupe">
            <label>Niveau :</label>
            <div class="radio-options">
                <label><input type="radio" name="lvl" value="Debutant" required> Débutant</label>
                <label><input type="radio" name="lvl" value="Confirme" required> Confirmé</label>
                <label><input type="radio" name="lvl" value="Expert" required> Expert</label>
            </div>
        </div>

        <div class="form-groupe">
            <label for="team">Équipe :</label>
            <select name="team" id="team" required>
                <option value="">-- Sélectionner une équipe --</option>
                <?php foreach ($listeEquipes as $equipe): ?>
                    <option value="<?= $equipe['id_equipe'] ?>">
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-actions">
            <input type="submit" value="S'inscrire" class="btn">
        </div>

        <div class="lien-connexion">
            Déjà inscrit ? <a href="index.php?action=connexion">Va dans le vestiaire (Connexion)</a>
        </div>

    </form>
</div>