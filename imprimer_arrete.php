<?php
include('db.php');

if (!isset($_GET['numAffect'])) {
    header("Location: affectations.php");
    exit();
}

$numAffect = $_GET['numAffect'];

// Fakana ny antsipiriany momba ilay affectation sy ny employé
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ARRÊTÉ N° <?= htmlspecialchars($data['numAffect']) ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            color: #000;
            background-color: #fff;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0;
        }
        .content {
            font-size: 16px;
            line-height: 1.8;
            text-align: justify;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .btn-print {
            background-color: #0275d8;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        /* Manafina ny bouton rehefa Imprimer-na ilay pejy */
        @media print {
            .btn-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print">🖨️ Imprimer / Enregistrer en PDF</button>

    <div class="header">
        REPOBLIKAN'I MADAGASIKARA<br>
        Fitiavana - Tanindrazana - Fandrosoana<br>
        -------------------<br>
        MINISTÈRE DE LA FONCTION PUBLIQUE
    </div>

    <div class="title">
        ARRÊTÉ N° <?= htmlspecialchars($data['numAffect']) ?><br>
        Portant affectation d'un agent de l'État
    </div>

    <div class="content">
        <p><b>LE MINISTRE,</b></p>
        <p>Vu la Constitution ;</p>
        <p>Vu le statut général des fonctionnaires ;</p>
        <p>Vu les nécessités de service ;</p>

        <p style="text-align: center; font-weight: bold; margin-top: 20px;">ARRÊTE :</p>

        <p><b>Article 1er :</b> M./Mme <b><?= htmlspecialchars($data['civilite'] . " " . $data['Nom'] . " " . $data['Prenom']) ?></b> (Matricule N° <?= htmlspecialchars($data['numEmp']) ?>), précédemment en service à <b><?= htmlspecialchars($data['old_prov'] . " - " . $data['old_des']) ?></b>, est affecté(e) auprès du service de <b><?= htmlspecialchars($data['new_prov'] . " - " . $data['new_des']) ?></b>.</p>

        <p><b>Article 2 :</b> L'intéressé(e) est tenu(e) de prendre son service à compter du <b><?= date('d/m/Y', strtotime($data['datePriseService'])) ?></b>.</p>

        <p><b>Article 3 :</b> Le présent arrêté sera enregistré et communiqué partout où besoin sera.</p>
    </div>

    <div class="signatures">
        <div>
            Fait le : <?= date('d/m/Y', strtotime($data['dateAffect'])) ?>
        </div>
        <div style="text-align: center; margin-right: 50px;">
            <b>Le Directeur des Ressources Humaines</b><br><br><br><br>
            <i>(Signature et Cachet)</i>
        </div>
    </div>

</body>
</html>