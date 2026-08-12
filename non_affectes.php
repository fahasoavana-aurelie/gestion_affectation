<?php include('index.php'); ?>
<h2>Liste des employés pas encore affectés</h2>

<?php
$sql = "SELECT * FROM EMPLOYE WHERE Lieu IS NULL OR numEmp NOT IN (SELECT DISTINCT numEmp FROM AFFECTER)";
$stmt = $pdo->query($sql);
$non_affectes = $stmt->fetchAll();

echo "<table><tr><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Poste</th><th>Mail</th></tr>";
foreach ($non_affectes as $row) {
    echo "<tr>
        <td>{$row['numEmp']}</td>
        <td>{$row['Nom']}</td>
        <td>{$row['Prenom']}</td>
        <td>{$row['poste']}</td>
        <td>{$row['mail']}</td>
    </tr>";
}
echo "</table>";
?>