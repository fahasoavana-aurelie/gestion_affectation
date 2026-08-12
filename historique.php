<?php include('index.php'); ?>
<h2>Historique des affectations</h2>

<h3>1. Par Employé</h3>
<form method="GET">
    <select name="numEmp">
        <option value="">-- Choisir un employé --</option>
        <?php
        $emps = $pdo->query("SELECT * FROM EMPLOYE")->fetchAll();
        foreach ($emps as $e) {
            echo "<option value='{$e['numEmp']}'>{$e['Nom']} {$e['Prenom']}</option>";
        }
        ?>
    </select>
    <button type="submit"  style="background-color: #2F7FDE ">Voir l'historique</button>
</form>

<?php
if (!empty($_GET['numEmp'])) {
    $stmt = $pdo->prepare("SELECT * FROM AFFECTER WHERE numEmp = ? ORDER BY dateAffect DESC");
    $stmt->execute([$_GET['numEmp']]);
    $affs = $stmt->fetchAll();

    echo "<table><tr><th>Code</th><th>Ancien Lieu</th><th>Nouveau Lieu</th><th>Date Affectation</th><th>Prise de Service</th><th>PDF</th></tr>";
    foreach ($affs as $a) {
        echo "<tr>
            <td>{$a['numAffect']}</td>
            <td>{$a['AncienLieu']}</td>
            <td>{$a['NouveauLieu']}</td>
            <td>{$a['dateAffect']}</td>
            <td>{$a['datePriseService']}</td>
            <td><a href='pdf_arrete.php?numAffect={$a['numAffect']}' target='_blank'>Générer Arrêté</a></td>
        </tr>";
    }
    echo "</table>";
}
?>

<hr>
<h3>2. Entre deux dates</h3>
<form method="GET">
    <input type="date" name="date1" required>
    <input type="date" name="date2" required>
    <button type="submit"  style="background-color: #2F7FDE ">Filtrer</button>
</form>

<?php
if (!empty($_GET['date1']) && !empty($_GET['date2'])) {
    $stmt = $pdo->prepare("SELECT a.*, e.Nom, e.Prenom FROM AFFECTER a JOIN EMPLOYE e ON a.numEmp = e.numEmp WHERE a.dateAffect BETWEEN ? AND ?");
    $stmt->execute([$_GET['date1'], $_GET['date2']]);
    $list = $stmt->fetchAll();

    echo "<table><tr><th>Employé</th><th>Ancien Lieu</th><th>Nouveau Lieu</th><th>Date</th></tr>";
    foreach ($list as $row) {
        echo "<tr>
            <td>{$row['Nom']} {$row['Prenom']}</td>
            <td>{$row['AncienLieu']}</td>
            <td>{$row['NouveauLieu']}</td>
            <td>{$row['dateAffect']}</td>
        </tr>";
    }
    echo "</table>";
}
?>