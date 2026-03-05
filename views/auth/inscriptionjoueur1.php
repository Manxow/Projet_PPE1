<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../models/ModelEquipe1.php';
$listeEquipes = ModelEquipe::getAllEquipes();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Page d'inscription</title>
</head>

<body>
    <div class="form">
        <h1>Vous voulez jouer ? </h1>
        <form action="index.php?action=traiter_inscription_joueur" method="POST">

            <label for="nom">Nom : </label>
            <input type="text" id="nom" name="nom" required><br><br>

            <label for="prenom">Prénom : </label>
            <input type="text" id="prenom" name="prenom" required><br><br>

            <label for="poste">Poste : </label>
            <input type="radio" id="poste" name="poste" value="Terrain"> Joueur de terrain
            <input type="radio" id="poste" name="poste" value="Gardien" required> Gardien <br><br>


            <label for="lvl">Niveau : </label>
            <input type="radio" id="lvl" name="lvl" value="Debutant"> Debutant
            <input type="radio" id="lvl" name="lvl" value="Confirme"> Confirme
            <input type="radio" id="lvl" name="lvl" value="Expert" required> Expert <br><br>

            <label for="team">Equipe : </label>
            <select name="team" id="team" required>
                <option value=""> Selectionner une équipe </option>
                <?php foreach ($listeEquipes as $equipe): ?>
                    <option value="<?= $equipe['id_equipe'] ?>">
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </option>
                <?php endforeach ?>
            </select>

            <br><br>

            <input type="submit" value="S'inscrire">
        </form>
    </div>
</body>











</html>