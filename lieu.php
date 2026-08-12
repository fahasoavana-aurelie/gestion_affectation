<?php 
include('db.php');
include('index.php');

$message = "";
$mode_edition = false;
$idlieu_edit = "";
$design_edit = "";
$province_edit = "";

// --- 1. TRAITEMENT SUPPRESSION ---
if (isset($_GET['supprimer'])) {
    $idlieu = $_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM LIEU WHERE idlieu = ?");
        $stmt->execute([$idlieu]);
        header("Location: lieu.php");
        exit();
    } catch (PDOException $e) {
        $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Ce lieu ne peut pas effacer parce que il utlise au EMPLOYE ou AFFECTATION!</div>";
    }
}

// --- 2. RECUPERATION DONNEES POUR MODIFICATION ---
if (isset($_GET['modifier'])) {
    $mode_edition = true;
    $idlieu_edit = $_GET['modifier'];
    
    $stmt = $pdo->prepare("SELECT * FROM LIEU WHERE idlieu = ?");
    $stmt->execute([$idlieu_edit]);
    $lieu_actuel = $stmt->fetch();
    
    if ($lieu_actuel) {
        $design_edit = $lieu_actuel['design'];
        $province_edit = $lieu_actuel['province'];
    }
}

// --- 3. TRAITEMENT AJOUT ET MODIFICATION ---
if (isset($_POST['enregistrer'])) {
    $idlieu = trim($_POST['idlieu']);
    $design = trim($_POST['design']);
    $province = trim($_POST['province']);
    $action = $_POST['action'];

    if ($action == 'ajouter') {
        try {
            $stmt = $pdo->prepare("INSERT INTO LIEU (idlieu, design, province) VALUES (?, ?, ?)");
            $stmt->execute([$idlieu, $design, $province]);
            header("Location: lieu.php");
            exit();
        } catch (PDOException $e) {
            // Fisorohana ny erreur amin'ny Clé Primaire Duplicate (Primary Key)
            if ($e->getCode() == 23000) {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Ce Code Lieu on déja enregistrer '$idlieu'! Entrez nouveau Code lieu.</div>";
            } else {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> " . $e->getMessage() . "</div>";
            }
        }
    } else if ($action == 'modifier') {
        try {
            $stmt = $pdo->prepare("UPDATE LIEU SET design = ?, province = ? WHERE idlieu = ?");
            $stmt->execute([$design, $province, $idlieu]);
            header("Location: lieu.php");
            exit();
        } catch (PDOException $e) {
            $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur de modification:</b> " . $e->getMessage() . "</div>";
        }
    }
}

// --- 4. RECUPERATION DE LA LISTE ---
$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY idlieu ASC")->fetchAll();
?>

<h2>Gestion des Lieux</h2>

<!-- Hafatra raha misy Erreur -->
<?= $message ?>

<!-- Formulaire (Ajout sy Modification) -->
<form method="POST" action="lieu.php">
    <h3><?= $mode_edition ? "Modifier le lieu (Code: $idlieu_edit)" : "Ajouter un lieu" ?></h3>
    
    <input type="hidden" name="action" value="<?= $mode_edition ? 'modifier' : 'ajouter' ?>">
    
    <label>Code Lieu (Primary Key) :</label>
    <input type="text" name="idlieu" value="<?= htmlspecialchars($idlieu_edit) ?>" <?= $mode_edition ? 'readonly style="background-color:#e9ecef;"' : 'required' ?> placeholder="ex: L01">
    
    <label>Désignation :</label>
    <input type="text" name="design" value="<?= htmlspecialchars($design_edit) ?>" required placeholder="ex: Bureau Central">
    
    <label>Province :</label>
    <input type="text" name="province" value="<?= htmlspecialchars($province_edit) ?>" required placeholder="ex: Antananarivo">
    
    <button type="submit" name="enregistrer" style="background-color: <?= $mode_edition ? '#f0ad4e' : '#2F7FD3' ?>; color: white; border: none; padding: 10px; cursor: pointer;">
        <?= $mode_edition ? 'Mettre à jour' : 'Enregistrer' ?>
    </button>
    
    <?php if ($mode_edition): ?>
        <a href="lieu.php" style="margin-left: 10px; text-decoration: none; color: #666;">Annuler</a>
    <?php endif; ?>
</form>

<hr>

<!-- Table d'affichage -->
<h3>Liste des lieux</h3>
<table>
    <tr>
        <th>Code Lieu</th>
        <th>Désignation</th>
        <th>Province</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($lieux as $l): ?>
    <tr>
        <td><b><?= htmlspecialchars($l['idlieu']) ?></b></td>
        <td><?= htmlspecialchars($l['design']) ?></td>
        <td><?= htmlspecialchars($l['province']) ?></td>
        <td>
            <a href="lieu.php?modifier=<?= urlencode($l['idlieu']) ?>" style="color: #f0ad4e; font-weight: bold; margin-right: 15px; text-decoration: none;">Modifier</a>
            <a href="lieu.php?supprimer=<?= urlencode($l['idlieu']) ?>" onclick="return confirm('Vous voulez supprimer cette lieu ?')" style="color: #d9534f; font-weight: bold; text-decoration: none;">Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>