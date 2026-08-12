<?php
include('db.php');

// 1. Antsoina ny PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_GET['numAffect'])) {
    header("Location: affectations.php");
    exit();
}

$numAffect = $_GET['numAffect'];

// 2. Fakana ny ampanazavana momba ilay affectation
$stmt = $pdo->prepare("
    SELECT a.*, e.civilite, e.Nom, e.Prenom, e.mail, 
           lNew.province AS new_prov, lNew.design AS new_des
    FROM affecter a
    JOIN EMPLOYE e ON a.numEmp = e.numEmp
    LEFT JOIN LIEU lNew ON a.NouveauLieu = lNew.idlieu
    WHERE a.numAffect = ?
");
$stmt->execute([$numAffect]);
$data = $stmt->fetch();

if (!$data) {
    echo "<h3>Affectation introuvable!</h3>";
    exit();
}

$message_resultat = "";

// 3. Fandefasana ny Mail rehefa cliqué ny bouton
if (isset($_POST['send_email'])) {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $body = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURATION SMTP GMAIL ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ⚠️ OVEY ETO NY EMAIL-NAO SY ILAY APP PASSWORD 16 LITERA:
        $mail->Username   = 'fahasoavana31@gmail.com'; 
        $mail->Password   = 'gofngvshdpfqunsj';        
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // --- DESTINATAIRE SY CONTENU ---
        $mail->setFrom('fahasoavana31@gmail.com', 'Direction RH');
        $mail->addAddress($to); 

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        // Ampio ity fizarana ity mba tsy hanakana ny connexion SSL ny Localhost
       $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

        $mail->send();
        $message_resultat = "<div style='color:green; background:#d4edda; padding:10px; border-radius:5px; margin-bottom:15px;'><b>Succès!</b> e-mail est envoyé à " . htmlspecialchars($to) . "</div>";
    } catch (Exception $e) {
        $message_resultat = "<div style='color:red; background:#f8d7da; padding:10px; border-radius:5px; margin-bottom:15px;'><b>Erreur:</b> Tsy lasa ny mail: {$mail->ErrorInfo}</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notification par Email</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .card { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #0275d8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background-color: #025aa5; }
    </style>
</head>
<body>

<div class="card">
    <h2>Notifier l'employé par Email</h2>

    <?= $message_resultat ?>

    <form method="POST">
        <label><b>Email Destinataire :</b></label>
        <input type="email" name="to" value="<?= htmlspecialchars($data['mail']) ?>" required>

        <label><b>Objet :</b></label>
        <input type="text" name="subject" value="Notification d'affectation - Arrêté N° <?= htmlspecialchars($data['numAffect']) ?>" required>

        <label><b>Message :</b></label>
        <textarea name="message" rows="7"><?= htmlspecialchars("Bonjour " . $data['civilite'] . " " . $data['Nom'] . " " . $data['Prenom'] . ",\n\nVous êtes affecté(e) à : " . $data['new_prov'] . " (" . $data['new_des'] . ").\nDate de prise de service : " . date('d/m/Y', strtotime($data['datePriseService'])) . ".\n\nCordialement,\nLa Direction RH.") ?></textarea>

        <button type="submit" name="send_email">Envoyer l'Email</button>
        <a href="affectations.php" style="margin-left: 10px; text-decoration: none; color: #666;">Retour</a>
    </form>
</div>

</body>
</html>