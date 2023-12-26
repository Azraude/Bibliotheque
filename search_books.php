<?php
// search_books.php

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=sql', 'root', '');

// Valeurs par défaut
$titre = '%';
$auteur = '%';
$editeur = '%';
$disponible = 'all';
$booksPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $booksPerPage;

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = empty($_POST['titre']) ? '%' : '%' . $_POST['titre'] . '%';
    $auteur = empty($_POST['auteur']) ? '%' : '%' . $_POST['auteur'] . '%';
    $editeur = empty($_POST['editeur']) ? '%' : '%' . $_POST['editeur'] . '%';
    $disponible = $_POST['disponible'];
}

// Construction de la requête SQL de base
$sql = "SELECT 
            livre.titre, 
            auteur.nom AS auteur_nom, 
            editeur.nom AS editeur_nom, 
            (SELECT COUNT(*) FROM emprunt WHERE emprunt.id_livre = livre.id AND emprunt.date_retour IS NULL) AS disponibilite, 
            (SELECT MAX(emprunt.date_emprunt) FROM emprunt WHERE emprunt.id_livre = livre.id) AS dernier_emprunt
        FROM 
            livre
        JOIN 
            auteur ON livre.id_auteur = auteur.id
        JOIN 
            editeur ON livre.id_editeur = editeur.id
        WHERE 
            livre.titre LIKE ? AND
            auteur.nom LIKE ? AND
            editeur.nom LIKE ?";

$params = [$titre, $auteur, $editeur]; // Paramètres pour la requête

// Ajouter la condition de disponibilité
if ($disponible !== 'all') {
    $sql .= " AND (SELECT COUNT(*) FROM emprunt WHERE emprunt.id_livre = livre.id AND emprunt.date_retour IS NULL) = ?";
    $params[] = ($disponible === '1' ? 0 : 1); // Ajouter le paramètre de disponibilité
}

// Ajouter la pagination
$sql .= " LIMIT $booksPerPage OFFSET $offset";

// Exécuter la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher les résultats
echo "<div class='book-container'>";
foreach ($books as $book) {
    echo "<div class='book'>";
    echo "<strong>Titre:</strong> " . htmlspecialchars($book['titre']) . "<br>";
    echo "<strong>Auteur:</strong> " . htmlspecialchars($book['auteur_nom']) . "<br>";
    echo "<strong>Éditeur:</strong> " . htmlspecialchars($book['editeur_nom']) . "<br>";
    echo "<strong>Disponible:</strong> " . ($book['disponibilite'] > 0 ? 'Non' : 'Oui') . "<br>";
    echo "<strong>Date du dernier emprunt:</strong> " . htmlspecialchars($book['dernier_emprunt']);
    echo "</div>";
}
echo "</div>";


// Calculer le nombre total de livres (en utilisant la même logique de filtre)
$sqlCount = "SELECT COUNT(*) 
             FROM livre 
             JOIN auteur ON livre.id_auteur = auteur.id
             JOIN editeur ON livre.id_editeur = editeur.id
             WHERE livre.titre LIKE ? AND auteur.nom LIKE ? AND editeur.nom LIKE ?";


$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$totalBooks = $stmt->fetchColumn();

// Calculer le nombre total de pages
$totalPages = ceil($totalBooks / $booksPerPage);

// Afficher les liens de pagination
echo "<div class='pagination'>";

// Afficher les liens de pagination
for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == $page) {
        echo "<a href='index.php?page=$i' class='page-link' style='text-decoration: underline;'>$i</a> ";
    } else {
        echo "<a href='index.php?page=$i' class='page-link'>$i</a> ";
    }
}
echo "</div>";
