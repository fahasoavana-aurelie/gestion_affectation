<?php 
include('db.php');
include('index.php');

$message = "";
$mode_edition = false;

// Variables ho an'ny formulaire
$numAffect_edit = "";
$numEmp_edit = "";
$AncienLieu_edit = "";
$NouveauLieu_edit = "";
$dateAffect_edit = date('Y-m-d');
$datePriseService_edit = date('Y-m-d');

// --- 1. TRAITEMENT SUPPRESSION ---
if (isset($_GET['supprimer'])) {
    $numAffect = $_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM affecter WHERE numAffect = ?");
        $stmt->execute([$numAffect]);
        header("Location: affectations.php");
        exit();
    } catch (PDOException $e) {
        $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Tsy afaka fafana ity affectation ity: " . $e->getMessage() . "</div>";
    }
}

// --- 2. RECUPERATION DONNEES POUR MODIFICATION ---
if (isset($_GET['modifier'])) {
    $mode_edition = true;
    $numAffect_edit = $_GET['modifier'];
    
    $stmt = $pdo->prepare("SELECT * FROM affecter WHERE numAffect = ?");
    $stmt->execute([$numAffect_edit]);
    $aff_actuel = $stmt->fetch();
    
    if ($aff_actuel) {
        $numEmp_edit = $aff_actuel['numEmp'];
        $AncienLieu_edit = $aff_actuel['AncienLieu'];
        $NouveauLieu_edit = $aff_actuel['NouveauLieu'];
        $dateAffect_edit = $aff_actuel['dateAffect'];
        $datePriseService_edit = $aff_actuel['datePriseService'];
    }
}

// --- 3. TRAITEMENT ENREGISTREMENT (AJOUT & MODIFICATION) ---
if (isset($_POST['enregistrer'])) {
    $action = $_POST['action'];
    $numEmp = $_POST['numEmp'];
    $NouveauLieu = $_POST['NouveauLieu'];
    $dateAffect = $_POST['dateAffect'];
    $datePriseService = $_POST['datePriseService'];

    // Sombiny hijerena ny AncienLieu (Lieu misy an'ilay employé amin'izao)
    $stmtEmp = $pdo->prepare("SELECT Lieu FROM EMPLOYE WHERE numEmp = ?");
    $stmtEmp->execute([$numEmp]);
    $empData = $stmtEmp->fetch();
    $AncienLieu = $empData ? $empData['Lieu'] : NULL;

    if ($action == 'ajouter') {
        $numAffect = trim($_POST['numAffect']);
        try {
            // 1. Ampidirina ao amin'ny table affecter
            $stmt = $pdo->prepare("INSERT INTO affecter (numAffect, numEmp, AncienLieu, NouveauLieu, dateAffect, datePriseService) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$numAffect, $numEmp, $AncienLieu, $NouveauLieu, $dateAffect, $datePriseService]);

            // 2. Mettre à jour ny Lieu ao amin'ny table EMPLOYE amin'ny NouveauLieu
            $stmtUpdateEmp = $pdo->prepare("UPDATE EMPLOYE SET Lieu = ? WHERE numEmp = ?");
            $stmtUpdateEmp->execute([$NouveauLieu, $numEmp]);

            header("Location: affectations.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Efa misy ny Numéro Affectation '$numAffect'!</div>";
            } else {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> " . $e->getMessage() . "</div>";
            }
        }
    } else if ($action == 'modifier') {
        $numAffect = $_POST['numAffect_hidden'];

        try {
            // 1. Update ao amin'ny table affecter
            $stmt = $pdo->prepare("UPDATE affecter SET numEmp = ?, NouveauLieu = ?, dateAffect = ?, datePriseService = ? WHERE numAffect = ?");
            $stmt->execute([$numEmp, $NouveauLieu, $dateAffect, $datePriseService, $numAffect]);

            // 2. Update ny Lieu ao amin'ny EMPLOYE
            $stmtUpdateEmp = $pdo->prepare("UPDATE EMPLOYE SET Lieu = ? WHERE numEmp = ?");
            $stmtUpdateEmp->execute([$NouveauLieu, $numEmp]);

            header("Location: affectations.php");
            exit();
        } catch (PDOException $e) {
            $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur amin'ny modification:</b> " . $e->getMessage() . "</div>";
        }
    }
}

// --- 4. RECUPERATION DES LISTES ---
$employes = $pdo->query("SELECT * FROM EMPLOYE ORDER BY Nom ASC")->fetchAll();
$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY province ASC")->fetchAll();

// Jointure miaraka amin'ny EMPLOYE sy LIEU amin'ny fampiasana NouveauLieu
$affectations = $pdo->query("
    SELECT a.*, e.Nom, e.Prenom, l.design, l.province 
    FROM affecter a
    JOIN EMPLOYE e ON a.numEmp = e.numEmp
    LEFT JOIN LIEU l ON a.NouveauLieu = l.idlieu
    ORDER BY a.dateAffect DESC
")->fetchAll();
?>

<h2>Gestion des Affectations</h2>

<?= $message ?>

<!-- Formulaire (Ajout sy Modification) -->
<form method="POST" action="affectations.php">
    <h3><?= $mode_edition ? "Modifier l'affectation (N° $numAffect_edit)" : "Nouvelle Affectation" ?></h3>
    
    <input type="hidden" name="action" value="<?= $mode_edition ? 'modifier' : 'ajouter' ?>">
    
    <?php if ($mode_edition): ?>
        <input type="hidden" name="numAffect_hidden" value="<?= htmlspecialchars($numAffect_edit) ?>">
    <?php endif; ?>

    <label>N° Affectation (Primary Key) :</label>
    <input type="text" name="numAffect" value="<?= htmlspecialchars($numAffect_edit) ?>" <?= $mode_edition ? 'disabled style="background-color:#e9ecef;"' : 'required' ?> placeholder="ex: AF01">

    <label>Employé :</label>
    <select name="numEmp" required>
        <option value="">-- Choisir un employé --</option>
        <?php foreach ($employes as $e): ?>
            <option value="<?= $e['numEmp'] ?>" <?= $numEmp_edit == $e['numEmp'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['numEmp'] . " - " . $e['Nom'] . " " . $e['Prenom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Nouveau Lieu :</label>
    <select name="NouveauLieu" required>
        <option value="">-- Choisir un lieu --</option>
        <?php foreach ($lieux as $l): ?>
            <option value="<?= $l['idlieu'] ?>" <?= $NouveauLieu_edit == $l['idlieu'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['idlieu'] . " - " . $l['province'] . " (" . $l['design'] . ")") ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Date Affectation :</label>
    <input type="date" name="dateAffect" value="<?= htmlspecialchars($dateAffect_edit) ?>" required>

    <label>Date Prise de Service :</label>
    <input type="date" name="datePriseService" value="<?= htmlspecialchars($datePriseService_edit) ?>" required>

    <br><br>
    <button type="submit" name="enregistrer" style="background-color: <?= $mode_edition ? '#f0ad4e' : '#2F7FD3' ?>; color: white; border: none; padding: 10px; cursor: pointer;">
        <?= $mode_edition ? 'Mettre à jour' : 'Enregistrer' ?>
    </button>
    
    <?php if ($mode_edition): ?>
        <a href="affectations.php" style="margin-left: 10px; text-decoration: none; color: #666;">Annuler</a>
    <?php endif; ?>
</form>

<hr>

<!-- Table d'affichage -->
<h3>Historique des Affectations</h3>
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr style="background-color: #f2f2f2;">
        <th>N° Affect</th>
        <th>Employé</th>
        <th>Ancien Lieu</th>
        <th>Nouveau Lieu</th>
        <th>Date Affect.</th>
        <th>Prise de Service</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($affectations as $a): ?>
    <tr>
        <td><b><?= htmlspecialchars($a['numAffect']) ?></b></td>
        <td><?= htmlspecialchars($a['numEmp'] . " - " . $a['Nom'] . " " . $a['Prenom']) ?></td>
        <td><?= htmlspecialchars($a['AncienLieu'] ? $a['AncienLieu'] : 'Aucun') ?></td>
        <td><?= htmlspecialchars($a['province'] . " (" . $a['design'] . ")") ?></td>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['dateAffect']))) ?></td>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['datePriseService']))) ?></td>
        <td>
            <!-- Bouton Modifier -->
            <a href="affectations.php?modifier=<?= urlencode($a['numAffect']) ?>" style="color: #f0ad4e; font-weight: bold; margin-right: 10px; text-decoration: none;">Modifier</a>
            
            <!-- Bouton Supprimer -->
            <a href="affectations.php?supprimer=<?= urlencode($a['numAffect']) ?>" onclick="return confirm('Vous voulez supprimer cette affectation ?')" style="color: #d9534f; font-weight: bold; margin-right: 10px; text-decoration: none;">Supprimer</a>

            <!-- Bouton Imprimer Arrêté -->
            
            <a href="notify_mail.php?numAffect=<?= urlencode($a['numAffect']) ?>" target="_blank" style="color: #0275d8; font-weight: bold; text-decoration: none;">Envoyer mail</a>
            <a href="imprimer_arrete.php?numAffect=<?= urlencode($a['numAffect']) ?>" target="_black" style="color: #0275d8; font-weight: bold; margin-left: 10px;">Générer Arrêté</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>