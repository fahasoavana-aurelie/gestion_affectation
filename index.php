<?php
session_start();

// Raha tsy mbola se connecter dia averina amin'ny login.php
if (!isset($_SESSION['user'])) {
    
    header("Location: login.php");
    exit();
}

include('db.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Affectations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        nav { background: #333; padding: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        nav .menu a { color: white; margin-right: 15px; text-decoration: none; font-weight: bold; }
        nav .user-info { color: #fff; font-size: 14px; }
        nav .logout-btn { background: #d9534f; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0275d8; color: white; }
        form { background: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        input, select, button { padding: 8px; margin: 5px 0; display: block; width: 100%; }
        button { background: #5cb85c; color: white; border: none; cursor: pointer; }

        /* --- STYLE NAMPIDIRINA HO AN'NY SARY SY BANNIERE --- */
        .banner-container {
            position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('images/banner.jpg'); /* Na rohy direct: url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80') */
            background-size: cover;
            background-position: center;
            height: 220px;
            border-radius: 8px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .banner-container h1 {
            font-size: 28px;
            margin: 0 0 10px 0;
            color: #ffffff;
        }
        .banner-container p {
            font-size: 16px;
            margin: 0;
            color: #e0e0e0;
        }

        /* Card Statistiques kely */
        .stats-box {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 5px solid #0275d8;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card h3 { margin: 0; font-size: 24px; color: #333; }
        .card p { margin: 5px 0 0 0; color: #666; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>

    <nav>
        <div class="menu">
            <a href="index.php">Accueil</a>
            <a href="lieu.php">Lieux</a>
            <a href="employes.php">Employés</a>
            <a href="affectations.php">Affectations</a>
            <a href="rechercher.php">Recherche</a>
            <a href="historique.php">Historique</a>
            <a href="non_affectes.php">Non Affectés</a>
        </div>
        <div class="user-info">
            Connecté : <b><?= htmlspecialchars($_SESSION['user'] ?? 'Utilisateur') ?></b>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </nav>

    <!-- BANNIERE MIARAKA AMIN'NY SARY -->
    <div class="banner-container">
        <h1>Bienvenue sur la page de gestion des Affectations</h1>
        <p>Système de gestion et de suivi des mutations des employés</p>
</div>

</body>
</html>