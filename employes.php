<?php 
include('db.php');
include('index.php');

$message = "";
$mode_edition = false;

// Variables ho an'ny formulaire
$numEmp_edit = "";
$civilite_edit = "Mr";
$Nom_edit = "";
$Prenom_edit = "";
$mail_edit = "";
$poste_edit = "";
$Lieu_edit = "";

// --- 1. TRAITEMENT SUPPRESSION ---
if (isset($_GET['supprimer'])) {
    $numEmp = $_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM EMPLOYE WHERE numEmp = ?");
        $stmt->execute([$numEmp]);
        header("Location: employes.php");
        exit();
    } catch (PDOException $e) {
        $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Tsy afaka fafana ity employé ity satria efa manana historique d'affectation!</div>";
    }
}

// --- 2. RECUPERATION DONNEES POUR MODIFICATION ---
if (isset($_GET['modifier'])) {
    $mode_edition = true;
    $numEmp_edit = $_GET['modifier'];
    
    $stmt = $pdo->prepare("SELECT * FROM EMPLOYE WHERE numEmp = ?");
    $stmt->execute([$numEmp_edit]);
    $emp_actuel = $stmt->fetch();
    
    if ($emp_actuel) {
        $civilite_edit = $emp_actuel['civilite'];
        $Nom_edit = $emp_actuel['Nom'];
        $Prenom_edit = $emp_actuel['Prenom'];
        $mail_edit = $emp_actuel['mail'];
        $poste_edit = $emp_actuel['poste'];
        $Lieu_edit = $emp_actuel['Lieu'];
    }
}

// --- 3. TRAITEMENT ENREGISTREMENT (AJOUT & MODIFICATION) ---
if (isset($_POST['enregistrer'])) {
    $action = $_POST['action'];
    $civilite = $_POST['civilite'];
    $Nom = trim($_POST['Nom']);
    $Prenom = trim($_POST['Prenom']);
    $mail = trim($_POST['mail']);
    $poste = trim($_POST['poste']);
    $Lieu = !empty($_POST['Lieu']) ? $_POST['Lieu'] : NULL;

    if ($action == 'ajouter') {
        $numEmp = trim($_POST['numEmp']);
        try {
            $stmt = $pdo->prepare("INSERT INTO EMPLOYE (numEmp, civilite, Nom, Prenom, mail, poste, Lieu) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$numEmp, $civilite, $Nom, $Prenom, $mail, $poste, $Lieu]);
            header("Location: employes.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> Efa misy ny Matricule Employé '$numEmp'!</div>";
            } else {
                $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur:</b> " . $e->getMessage() . "</div>";
            }
        }
    } else if ($action == 'modifier') {
        // Raisina avy amin'ny input hidden ny numEmp rehefa modifier
        $numEmp = $_POST['numEmp_hidden'];
        
        try {
            $stmt = $pdo->prepare("UPDATE EMPLOYE SET civilite = ?, Nom = ?, Prenom = ?, mail = ?, poste = ?, Lieu = ? WHERE numEmp = ?");
            $stmt->execute([$civilite, $Nom, $Prenom, $mail, $poste, $Lieu, $numEmp]);
            header("Location: employes.php");
            exit();
        } catch (PDOException $e) {
            $message = "<div style='color:red; margin-bottom:15px;'><b>Erreur amin'ny modification:</b> " . $e->getMessage() . "</div>";
        }
    }
}

// --- 4. RECUPERATION DES DONNEES ---
$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY province ASC")->fetchAll();
$employes = $pdo->query("
    SELECT e.*, l.design AS nom_lieu, l.province 
    FROM EMPLOYE e 
    LEFT JOIN LIEU l ON e.Lieu = l.idlieu 
    ORDER BY e.numEmp ASC
")->fetchAll();
?>

<h2>Gestion des Employés</h2>

<!-- Hafatra raha misy Erreur -->
<?= $message ?>

<!-- Formulaire (Ajout sy Modification) -->
<form method="POST" action="employes.php">
    <h3><?= $mode_edition ? "Modifier l'employé (Matricule: $numEmp_edit)" : "Ajouter un nouvel employé" ?></h3>
    
    <!-- Action hidden: ajouter na modifier -->
    <input type="hidden" name="action" value="<?= $mode_edition ? 'modifier' : 'ajouter' ?>">
    
    <?php if ($mode_edition): ?>
        <!-- Hidden input ho an'ny numEmp mba tsy ho very rehefa modifier -->
        <input type="hidden" name="numEmp_hidden" value="<?= htmlspecialchars($numEmp_edit) ?>">
    <?php endif; ?>

    <label>Matricule (Primary Key) :</label>
    <input type="text" name="numEmp" value="<?= htmlspecialchars($numEmp_edit) ?>" <?= $mode_edition ? 'disabled style="background-color:#e9ecef;"' : 'required' ?> placeholder="ex: E01">
    
    <label>Civilité :</label>
    <select name="civilite" required>
        <option value="Mr" <?= $civilite_edit == 'Mr' ? 'selected' : '' ?>>Mr</option>
        <option value="Mlle" <?= $civilite_edit == 'Mlle' ? 'selected' : '' ?>>Mlle</option>
        <option value="Mme" <?= $civilite_edit == 'Mme' ? 'selected' : '' ?>>Mme</option>
    </select>

    <label>Nom :</label>
    <input type="text" name="Nom" value="<?= htmlspecialchars($Nom_edit) ?>" required placeholder="ex: RAKOTO">

    <label>Prénom :</label>
    <input type="text" name="Prenom" value="<?= htmlspecialchars($Prenom_edit) ?>" required placeholder="ex: Bernard">

    <label>Adresse Email :</label>
    <input type="email" name="mail" value="<?= htmlspecialchars($mail_edit) ?>" required placeholder="ex: rakoto@gmail.com">

    <label>Poste occupé :</label>
    <input type="text" name="poste" value="<?= htmlspecialchars($poste_edit) ?>" required placeholder="ex: Réceptionniste">

    <label>Lieu d'affectation :</label>
    <select name="Lieu">
        <option value="">-- Aucun / Non affecté --</option>
        <?php foreach ($lieux as $l): ?>
            <option value="<?= $l['idlieu'] ?>" <?= $Lieu_edit == $l['idlieu'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['idlieu'] . " - " . $l['province'] . " (" . $l['design'] . ")") ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>
    <button type="submit" name="enregistrer" style="background-color: <?= $mode_edition ? '#f0ad4e' : '#2F7FDE' ?>; color: white; border: none; padding: 10px; cursor: pointer;">
        <?= $mode_edition ? 'Mettre à jour' : 'Enregistrer' ?>
    </button>
    
    <?php if ($mode_edition): ?>
        <a href="employes.php" style="margin-left: 10px; text-decoration: none; color: #666;">Annuler</a>
    <?php endif; ?>
</form>

<hr>

<!-- Table d'affichage -->
<h3>Liste des employés</h3>
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr style="background-color: #f2f2f2;">
        <th>Matricule</th>
        <th>Civilité</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Mail</th>
        <th>Poste</th>
        <th>Lieu Actuel</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($employes as $e): ?>
    <tr>
        <td><b><?= htmlspecialchars($e['numEmp']) ?></b></td>
        <td><?= htmlspecialchars($e['civilite']) ?></td>
        <td><?= htmlspecialchars($e['Nom']) ?></td>
        <td><?= htmlspecialchars($e['Prenom']) ?></td>
        <td><?= htmlspecialchars($e['mail']) ?></td>
        <td><?= htmlspecialchars($e['poste']) ?></td>
        <td>
            <?= $e['Lieu'] ? htmlspecialchars($e['province'] . " (" . $e['nom_lieu'] . ")") : '<i style="color:gray;">Non affecté</i>' ?>
        </td>
        <td>
            <a href="employes.php?modifier=<?= urlencode($e['numEmp']) ?>" style="color: #f0ad4e; font-weight: bold; margin-right: 15px; text-decoration: none;">Modifier</a>
            <a href="employes.php?supprimer=<?= urlencode($e['numEmp']) ?>" onclick="return confirm('Vous voulez supprimer cette employe ?')" style="color: #d9534f; font-weight: bold; text-decoration: none;">Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>