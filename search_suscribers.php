<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=sql', 'root', '');

// Définition des variables pour la recherche et la pagination
$nom = '%';
$prenom = '%';
$ville = '%';
$abonnement = 'all';
$abonnesPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $abonnesPerPage;

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = empty($_POST['nom']) ? '%' : '%' . $_POST['nom'] . '%';
    $prenom = empty($_POST['prenom']) ? '%' : '%' . $_POST['prenom'] . '%';
    $ville = empty($_POST['ville']) ? '%' : '%' . $_POST['ville'] . '%';
    $abonnement = $_POST['abonnement'];
}

// Construction de la requête SQL de base
$sql = "SELECT 
            abonne.id,
            abonne.nom, 
            abonne.prenom, 
            abonne.ville, 
            abonne.date_naissance,
            abonne.date_fin_abo
        FROM 
            abonne
        WHERE 
            LOWER(abonne.nom) LIKE LOWER(?) AND
            LOWER(abonne.prenom) LIKE LOWER(?) AND
            LOWER(abonne.ville) LIKE LOWER(?)";


$params = [$nom, $prenom, $ville]; // Paramètres pour la requête

// Ajouter la condition d'abonnement
if ($abonnement !== 'all') {
    if ($abonnement == 'abonne') {
        $sql .= " AND abonne.date_fin_abo >= CURDATE()";
    } else if ($abonnement == 'expire') {
        $sql .= " AND abonne.date_fin_abo < CURDATE()";
    }
}

// Ajouter la pagination
$sql .= " LIMIT $abonnesPerPage OFFSET $offset";

// Exécuter la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$abonnes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher les résultats
echo "<div class='abonne-container'>";
foreach ($abonnes as $abonne) {
    echo "<div class='abonne'>";

    // Ajouter un lien autour du nom de l'abonné
    echo "<a href='abonne_detail.php?id=" . htmlspecialchars($abonne['id']) . "'>";
    echo "<strong>Nom:</strong> " . htmlspecialchars($abonne['nom']) . "</a><br>";

    echo "<strong>Prénom:</strong> " . htmlspecialchars($abonne['prenom']) . "<br>";
    echo "<strong>Ville:</strong> " . htmlspecialchars($abonne['ville']) . "<br>";
    echo "<strong>Date de naissance:</strong> " . htmlspecialchars($abonne['date_naissance']) . "<br>";
    echo "<strong>Date fin abonnement:</strong> " . htmlspecialchars($abonne['date_fin_abo']);
    echo "</div>";
}
echo "</div>";


// Ajout de contrôles de pagination
$sqlCount = "SELECT COUNT(*) 
             FROM abonne
             WHERE abonne.nom LIKE ? AND abonne.prenom LIKE ? AND abonne.ville LIKE ?";

if ($abonnement !== 'all') {
    if ($abonnement == 'abonne') {
        $sql .= " AND abonne.date_fin_abo >= CURDATE()";
    } else if ($abonnement == 'expire') {
        $sql .= " AND abonne.date_fin_abo < CURDATE()";
    }
}


$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$totalAbonnes = $stmt->fetchColumn();

// Calculer le nombre total de pages
$totalPages = ceil($totalAbonnes / $abonnesPerPage);

// Afficher les liens de pagination
echo "<div class='pagination'>";
for ($i = 1; $i <= $totalPages; $i++) {
    echo "<a href='abonne.php?page=$i' class='page-link'>$i</a> ";
}
echo "</div>";
