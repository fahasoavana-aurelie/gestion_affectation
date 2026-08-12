<?php include('index.php'); ?>
<h2>Recherche d'un employé</h2>

<form method="GET">
    <input type="text" name="q" placeholder="Nom ou Prénom..." required>
    <button type="submit" style="background-color: #2F7FDE ">Rechercher</button>
</form>

<?php
if (isset($_GET['q'])) {
    $q = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare("SELECT * FROM EMPLOYE WHERE Nom LIKE :q OR Prenom LIKE :q");
    $stmt->execute(['q' => $q]);
    $results = $stmt->fetchAll();

    echo "<h3>Résultats :</h3><table><tr><th>Matricule</th><th>Civilité</th><th>Nom</th><th>Prénom</th><th>Mail</th><th>Poste</th></tr>";
    foreach ($results as $row) {
        echo "<tr>
            <td>{$row['numEmp']}</td>
            <td>{$row['civilite']}</td>
            <td>{$row['Nom']}</td>
            <td>{$row['Prenom']}</td>
            <td>{$row['mail']}</td>
            <td>{$row['poste']}</td>
        </tr>";
    }
    echo "</table>";
}
?>