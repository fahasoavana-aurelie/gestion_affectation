<?php
include('db.php');

if (!isset($_GET['numAffect'])) die("Référence d'affectation manquante.");

$numAffect = $_GET['numAffect'];
$stmt = $pdo->prepare("
    SELECT a.*, e.civilite, e.Nom, e.Prenom, e.poste, e.mail 
    FROM AFFECTER a 
    JOIN EMPLOYE e ON a.numEmp = e.numEmp 
    WHERE a.numAffect = ?
");
$stmt->execute([$numAffect]);
$data = $stmt->fetch();

if (!$data) die("Affectation introuvable.");

// Notification par mail (requiert la configuration SMTP dans php.ini pour XAMPP)
$to = $data['mail'];
$subject = "Notification d'affectation - Arrêté N°" . $data['numAffect'];
$message = "Bonjour " . $data['civilite'] . " " . $data['Nom'] . ",\n\nVous êtes nouvellement affecté à " . $data['NouveauLieu'] . " à compter du " . date('d/m/Y', strtotime($data['datePriseService'])) . ".";
$headers = "From: drh@organisme.mg";

@mail($to, $subject, $message, $headers); // Le '@' évite le crash si le serveur SMTP local n'est pas configuré
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Arrêté N°<?php echo $data['numAffect']; ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; padding: 40px; line-height: 1.6; }
        .title { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 30px; }
        .content { font-size: 14px; text-align: justify; margin-bottom: 40px; }
        .btn-print { margin-bottom: 20px; padding: 10px 15px; background: #0275d8; color: white; border: none; cursor: pointer; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Imprimer / Enregistrer en PDF</button>

<div class="title">
    Arrêté N°<?php echo $data['numAffect']; ?> du <?php echo date('d/m/Y', strtotime($data['dateAffect'])); ?>
</div>

<div class="content">
    <?php echo $data['civilite']; ?> <b><?php echo $data['Nom'] . " " . $data['Prenom']; ?></b>, qui occupe le poste de 
    <b><?php echo $data['poste']; ?></b> à <b><?php echo $data['AncienLieu']; ?></b>, est affecté à 
    <b><?php echo $data['NouveauLieu']; ?></b> pour compter de la date de prise de service 
    <b><?php echo date('d/m/Y', strtotime($data['datePriseService'])); ?></b>.
</div>

<div class="content">
    Le présent communiqué sera enregistré et communiqué partout où besoin sera.
</div>

</body>
</html>