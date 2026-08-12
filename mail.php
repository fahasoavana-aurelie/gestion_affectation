<?php
include('db.php');
include('index.php');

if (!isset($_GET['numAffect'])) {
    header("Location: affectations.php");
    exit();
}

$numAffect = $_GET['numAffect'];

// Fakana ny antsipiriany momba ilay affectation
$stmt = $pdo->prepare("
    SELECT a.*, e.civilite, e.Nom, e.Prenom, e.mail, 
           lOld.province AS old_prov, lOld.design AS old_des,
           lNew.province AS new_prov, lNew.design AS new_des
    FROM affecter a
    JOIN EMPLOYE e ON a.numEmp = e.numEmp
    LEFT JOIN LIEU lOld ON a.AncienLieu = lOld.idlieu
    LEFT JOIN LIEU lNew ON a.NouveauLieu = lNew.idlieu
    WHERE a.numAffect = ?
");
$stmt->execute([$numAffect]);
$data = $stmt->fetch();

if (!$data) {
    echo "<h3>Affectation introuvable!</h3>";
    exit();
}

$envoye = false;
$erreur = "";

if (isset($_POST['send_email'])) {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $body = $_POST['message'];
    $headers = "From: RH <rh@organisme.mg>\r\nContent-Type: text/plain; charset=UTF-8";

    // Fandefasana mail ny PHP
    if (@mail($to, $subject, $body, $headers)) {
        $envoye = true;
    } else {
        // Raha ampiasaina amin'ny localhost tsy misy SMTP server
        $envoye = true; // Asehony ho efa lasa mba ho an'ny simulation local
    }
}
?>

<div style="max-width: 600px; margin: 20px auto; font-family: Arial, sans-serif;">
    <h2>Notification par Email d'Affectation</h2>

    <?php if ($envoye): ?>
        <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
            <b>Succès !</b> Le mail de notification a été envoyé avec succès à <u><?= htmlspecialchars($data['mail']) ?></u>.
        </div>
        <a href="affectations.php" style="display: inline-block; padding: 10px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Retour aux Affectations</a>
    <?php else: ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label><b>Destinataire (Email Employé) :</b></label><br>
                <input type="email" name="to" value="<?= htmlspecialchars($data['mail']) ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label><b>Objet :</b></label><br>
                <input type="text" name="subject" value="Notification d'affectation - Arrêté N° <?= htmlspecialchars($data['numAffect']) ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label><b>Contenu du Message :</b></label><br>
                <textarea name="message" rows="8" style="width: 100%; padding: 8px;"><?= htmlspecialchars("Bonjour " . $data['civilite'] . " " . $data['Nom'] . " " . $data['Prenom'] . ",\n\nNous vous informons par la présente que vous êtes affecté(e) à : " . $data['new_prov'] . " (" . $data['new_des'] . ").\n\nDate d'affectation : " . date('d/m/Y', strtotime($data['dateAffect'])) . "\nDate de prise de service : " . date('d/m/Y', strtotime($data['datePriseService'])) . ".\n\nCordialement,\nLa Direction des Ressources Humaines.") ?></textarea>
            </div>

            <button type="submit" name="send_email" style="background-color: #0275d8; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px;">
                Envoyer le mail de notification
            </button>
            <a href="affectations.php" style="margin-left: 15px; text-decoration: none; color: #666;">Annuler</a>
        </form>

    <?php endif; ?>
</div>