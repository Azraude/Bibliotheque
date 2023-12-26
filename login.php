<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="login-container">
        <h2>Connexion</h2>
        <form class="login-form" action="login.php" method="post">
            <div class="form-group">
                <label for="nom_utilisateur">Nom d'utilisateur:</label>
                <input class="form-control" type="text" id="nom_utilisateur" name="nom_utilisateur" required>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe:</label>
                <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <input class="btn-login" type="submit" value="Se connecter">
        </form>
    </div>


    <?php
    session_start(); // Démarrer une session

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pdo = new PDO('mysql:host=localhost;dbname=sql', 'root', '');

        $nom_utilisateur = $_POST['nom_utilisateur'];
        $mot_de_passe = $_POST['mot_de_passe'];

        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$nom_utilisateur]);
        $utilisateur = $stmt->fetch();

        if ($utilisateur && $mot_de_passe == $utilisateur['mot_de_passe']) {
            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_type'] = $utilisateur['type'];

            // Rediriger vers une page différente en fonction du type d'utilisateur
            if ($utilisateur['type'] == 'gestionnaire') {
                header('Location: index.php'); // Remplacez par la page réelle du gestionnaire
                exit;
            } else if ($utilisateur['type'] == 'abonne') {
                header('Location: abonne.php'); // Remplacez par la page réelle de l'abonné
                exit;
            }
        } else {
            echo "Identifiants incorrects.";
        }
    }
    ?>



</body>

</html>