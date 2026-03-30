<?php

session_start();


header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");


require_once '../models/ModelJoueur1.php';
require_once '../models/ModelEquipe1.php';

if (!isset($monJoueur)) {
    header("Location: connexion.php");
    exit();
}

$listeEquipes = ModelEquipe::getAllEquipes();
$joueurActuel = ModelJoueur::getJoueurById($_SESSION['id_joueur']);


$monJoueur = new ModelJoueur($joueurActuel['user'], $joueurActuel['pw'], $joueurActuel['nom'], $joueurActuel['prenom'], $joueurActuel['poste'], $joueurActuel['niveau'], $joueurActuel['id_equipe']);
$listeEquipes = ModelEquipe::getAllEquipes();
$equipeId = $monJoueur->getEquipe();
$_SESSION['idTeam'] = $equipeId;
$equipeDuJoueur = new ModelEquipe($equipeId);
$nomTeam = $equipeDuJoueur->getNomEquipe();
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
            <input type="text" id="nom" name="nom" value="<?php echo $monJoueur->getNom(); ?>" required><br><br>

            <label for="prenom">Prenom : </label>
            <input type="text" id="prenom" name="prenom" value="<?php echo  $monJoueur->getPrenom(); ?>" required><br><br>

            <label for="poste">Poste : </label>
            <input type="radio" id="poste" name="poste" value="Terrain"
                <?php if ($monJoueur->getPoste() == "Terrain") {
                ?> checked
                <?php
                }
                ?>> Joueur de terrain
            <input type="radio" id="poste" name="poste" value="Gardien"
                <?php if ($monJoueur->getPoste() == "Gardien") {
                ?> checked
                <?php
                } ?> required> Gardien <br><br>


            <label for="lvl">Niveau : <?php echo htmlspecialchars($monJoueur->getNiveau()); ?></label>
            <input type="radio" id="lvl_debutant" name="lvl" value="Debutant"
                <?php if ($monJoueur->getNiveau() == "Debutant") {
                ?> checked
                <?php
                } ?>> Debutant
            <input type="radio" id="lvl_confirme" name="lvl" value="Confirme"
                <?php if ($monJoueur->getNiveau() == "Confirme") {
                ?> checked
                <?php
                } ?>> Confirme
            <input type="radio" id="lvl_expert" name="lvl" value="Expert"
                <?php if ($monJoueur->getNiveau() == "Expert") {
                ?> checked
                <?php
                } ?> required> Expert <br><br>

            <label for="team">Equipe : </label>
            <select name="team" id="team" required>
                <option value="">Sélectionner</option>
                <?php foreach ($listeEquipes as $equipe): ?>
                    <option value="<?= $equipe['id_equipe'] ?>" <?php if (isset($monJoueur) && $monJoueur->getEquipe() == $equipe['id_equipe']) echo 'selected'; ?>>
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </option>
                <?php endforeach ?>
            </select>

            <?php if (!empty($nomTeam) && $nomTeam !== 'Aucune'): ?>
                <form action="index.php?action=quitter_equipe" method="POST" onsubmit="return confirm('Es-tu sûr de vouloir quitter l\'équipe \'<?= addslashes(htmlspecialchars($nomTeam)) ?>\' ?');">
                    <button type="submit" class="btn btn-rouge mt-40">Quitter mon équipe</button>
                </form>
            <?php endif; ?>
            <br><br>

            <input type="submit" value="Modifier">
        </form>
    </div>
</body>

</html>