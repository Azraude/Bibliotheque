<?php
session_start(); // Assurez-vous de démarrer la session

// Vérifiez si l'utilisateur est connecté et est un gestionnaire
$estGestionnaire = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'gestionnaire';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Recherche d'abonné</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class='navbar'>
        <a href="index.php">Livre</a>
        <?php if ($estGestionnaire) : ?>
            <!-- Affichez l'option de recherche d'abonnés seulement pour les gestionnaires -->
            <a href="abonne.php">Abonne</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])) : ?>
            <!-- L'utilisateur est connecté, afficher le bouton de déconnexion -->
            <a href="deconnexion.php">Déconnexion</a>
        <?php else : ?>
            <!-- L'utilisateur est déconnecté, afficher le bouton de connexion -->
            <a href="login.php">Connexion</a>
        <?php endif; ?>
    </div>
    <h1>Recherche d'abonné</h1>
    <form method="post">
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom">

        <label for="prenom">Prénom:</label>
        <input type="text" id="prenom" name="prenom">

        <label for="ville">Ville:</label>
        <input type="text" id="ville" name="ville">

        <label for="abonnement">Abonnement:</label>
        <select id="abonnement" name="abonnement">
            <option value="all">Tous</option>
            <option value="abonne">Abonné</option>
            <option value="expire">Expiré</option>
        </select>

        <input type="submit" value="Rechercher">
    </form>

    <?php include 'search_suscribers.php'; ?>
</body>

</html>