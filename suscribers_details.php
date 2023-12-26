<?php
$pdo = new PDO('mysql:host=localhost;dbname=sql', 'root', '');

// Récupération de l'ID de l'abonné et validation
$abonne_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Préparation de la requête pour récupérer les informations de l'abonné
$stmt = $pdo->prepare("SELECT * FROM abonne WHERE id = ?");
$stmt->execute([$abonne_id]);
$abonne = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST" && $abonne_id > 0) {
    // Récupération des données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';
    $date_fin_abo = $_POST['date_fin_abo'] ?? '';

    // Mise à jour de l'abonné dans la base de données
    $updateStmt = $pdo->prepare("UPDATE abonne SET nom = ?, prenom = ?, ville = ?, adresse = ?, code_postal = ?, date_naissance = ?, date_fin_abo = ? WHERE id = ?");
    $updateStmt->execute([$nom, $prenom, $ville, $adresse, $code_postal, $date_naissance, $date_fin_abo, $abonne_id]);


    echo "Informations mises à jour.";
    header('Location: abonne_detail.php?id=' . $abonne_id);
    exit;
}
// S'assurer que l'abonné existe
if (!$abonne) {
    echo "Aucun abonné trouvé.";
    exit; // Arrête l'exécution du script si aucun abonné n'est trouvé
}

// Requête pour les livres empruntés
$stmt = $pdo->prepare("
    SELECT livre.titre, emprunt.date_emprunt 
    FROM emprunt 
    JOIN livre ON emprunt.id_livre = livre.id 
    WHERE emprunt.id_abonne = ? 
    ORDER BY emprunt.date_emprunt DESC
");
$stmt->execute([$abonne_id]);
$livres_empruntes = $stmt->fetchAll();

// Formulaire pour afficher et modifier les informations de l'abonné
echo "<form action='abonne_detail.php?id=" . htmlspecialchars($abonne_id) . "' method='post'>";
echo "<label class='label_details' for='nom'>Nom:</label>";
echo "<input type='text' class='input_details' id='nom' name='nom' value='" . htmlspecialchars($abonne['nom']) . "'><br>";

echo "<label class='label_details' for='prenom'>Prénom:</label>";
echo "<input type='text' class='input_details' id='prenom' name='prenom' value='" . htmlspecialchars($abonne['prenom']) . "'><br>";

echo "<label class='label_details' for='ville'>Ville:</label>";
echo "<input type='text' class='input_details' id='ville' name='ville' value='" . htmlspecialchars($abonne['ville']) . "'><br>";

echo "<label class='label_details' for='adresse'>Adresse:</label>";
echo "<input type='text' class='input_details' id='adresse' name='adresse' value='" . htmlspecialchars($abonne['adresse']) . "'><br>";

echo "<label class='label_details' for='code_postal'>Code Postal:</label>";
echo "<input type='text' class='input_details' id='code_postal' name='code_postal' value='" . htmlspecialchars($abonne['code_postal']) . "'><br>";

echo "<label class='label_details' for='date_naissance'>Date de Naissance:</label>";
echo "<input type='date' class='input_details' id='date_naissance' name='date_naissance' value='" . htmlspecialchars($abonne['date_naissance']) . "'><br>";

echo "<label class='label_details' for='date_fin_abo'>Date Fin Abonnement:</label>";
echo "<input type='date' class='input_details' id='date_fin_abo' name='date_fin_abo' value='" . htmlspecialchars($abonne['date_fin_abo']) . "'><br>";



echo "<input type='submit' class='input_submit' value='Enregistrer les modifications'>";
echo "</form>";

echo "<h2>Livres Empruntés</h2>";
foreach ($livres_empruntes as $livre) {
    echo htmlspecialchars($livre['titre']) . " - Emprunté le: " . htmlspecialchars($livre['date_emprunt']) . "<br>";
}




// 2. Liste de suggestions de livres
// a. Trouver la catégorie préférée
// Trouver le genre le plus emprunté
$stmt = $pdo->prepare("
SELECT livre.genre
FROM emprunt
JOIN livre ON emprunt.id_livre = livre.id
WHERE emprunt.id_abonne = ?
GROUP BY livre.genre
ORDER BY COUNT(*) DESC
LIMIT 1
");
$stmt->execute([$abonne_id]);
$genre_prefere = $stmt->fetchColumn();

echo "<h2>Genre Préféré: " . htmlspecialchars($genre_prefere) . "</h2>";

// Trouver les livres populaires dans ce genre
$stmt = $pdo->prepare("
SELECT livre.titre
FROM livre
LEFT JOIN emprunt ON livre.id = emprunt.id_livre AND emprunt.date_retour IS NULL
WHERE livre.genre = ? AND emprunt.id_livre IS NULL
GROUP BY livre.titre
ORDER BY COUNT(emprunt.id_livre) DESC
LIMIT 5
");
$stmt->execute([$genre_prefere]);
$livres_suggestions = $stmt->fetchAll();

echo "<h2>Suggestions de Livres</h2>";
foreach ($livres_suggestions as $livre) {
    echo htmlspecialchars($livre['titre']) . "<br>";
}
