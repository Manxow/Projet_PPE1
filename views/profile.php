<?php

session_start();


header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");


require_once '../models/ModelJoueur1.php';
require_once '../models/ModelEquipe1.php';

if (!isset($_SESSION['joueur'])) {
    header("Location: connexion.php");
    exit();
}

$listeEquipes = ModelEquipe::getAllEquipes();
?>

<!DOCTYPE html>
<html lang="fr">

<body>

    <?php
    echo "Bonjour " . $_SESSION['nom_utilisateur'];
    ?>

    <div class="form">
        <h1>Votre profil </h1>
        <form action="index.php?action=traiter_profile" method="POST">

            <label for="nom">Nom : </label>
            <input type="text" id="nom" name="nom" value="<?php echo $_SESSION['joueur']->getNom(); ?>" required><br><br>

            <label for="prenom">Prenom : </label>
            <input type="text" id="prenom" name="prenom" value="<?php echo  $_SESSION['joueur']->getPrenom(); ?>" required><br><br>

            <label for="poste">Poste : </label>
            <input type="radio" id="poste" name="poste" value="Terrain"
                <?php if ($_SESSION['joueur']->getPoste() == "Terrain") {
                ?> checked
                <?php
                }
                ?>> Joueur de terrain
            <input type="radio" id="poste" name="poste" value="Gardien"
                <?php if ($_SESSION['joueur']->getPoste() == "Gardien") {
                ?> checked
                <?php
                } ?> required> Gardien <br><br>


            <label for="lvl">Niveau : <?php $_SESSION['joueur']->getNiveau() ?></label>
            <input type="radio" id="lvl" name="lvl" value="Debutant"
                <?php if ($_SESSION['joueur']->getNiveau() == "Debutant") {
                ?> checked
                <?php
                } ?>> Debutant
            <input type="radio" id="lvl" name="lvl" value="Confirme"
                <?php if ($_SESSION['joueur']->getNiveau() == "Confirme") {
                ?> checked
                <?php
                } ?>> Confirme
            <input type="radio" id="lvl" name="lvl" value="Expert"
                <?php if ($_SESSION['joueur']->getNiveau() == "Expert") {
                ?> checked
                <?php
                } ?> required> Expert <br><br>

            <label for="team">Equipe : </label>
            <select name="team" id="team" required>
                <option value=""> <?php echo $_SESSION['nomTeam'] ?></option>
                <?php foreach ($listeEquipes as $equipe): ?>
                    <option value="<?= $equipe['id_equipe'] ?>">
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </option>
                <?php endforeach ?>
            </select>

            <br><br>

            <input type="submit" value="Modifier">
        </form>
    </div>
</body>

</html>