<?php require_once '../models/ModelEquipe1.php';
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
        <h1>Inscription</h1>
        <form action="index.php?action=traiter_inscription" method="POST">
            <label for="user">Nom d'utilisateur : </label>
            <input type="text" id="user" name="user" required><br><br>

            <label for="pw">Mot de passe : </label>
            <input type="text" id="pw" name="pw" required><br><br>


            <br><br>

            <input type="submit" value="S'inscrire">

            <div class="links">
                Déjà Inscrit ? <a href="index.php?action=connexion"> Connectez-vous ici </a>
            </div>
    </div>






    </form>
</body>






</html>