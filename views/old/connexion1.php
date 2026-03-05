<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Page de connexion</title>
</head>

<body>
    <div class="form">
        <h1>Connexion</h1>
        <form action="index.php?action=traiter_connexion" method="POST">
            <label for="user">Nom d'utilisateur : </label>
            <input type="text" id="user" name="user" required> <br><br>

            <label for="pw">Mot de passe : </label>
            <input type="text" id="pw" name="pw" required><br><br>

            <input type="submit" value="Se connecter">

            <div class="links">
                Pas encore inscrit ? <a href="index.php?action=inscription"> Incrivez-vous ici</a>
            </div>

    </div>



    </form>
</body>






</html>