<?php
session_start();
include('db.php');

$erreur = "";

if (isset($_POST['connexion'])) {
    $utilisateur = trim($_POST['utilisateur']);
    $mot_de_passe = trim($_POST['Mot_de_passe']);

    if (!empty($utilisateur) && !empty($mot_de_passe)) {
        // Hamarina ao amin'ny table ny nom d'utilisateur sy mot de passe
        // Mariho: raha "UTILISATEUR" ny anaran'ny table-nao dia ovay eto
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur = ? AND Mot_de_passe = ?");
        $stmt->execute([$utilisateur, $mot_de_passe]);
        $user = $stmt->fetch();

        if ($user) {
            // Raha marina ny login sy mot de passe
            $_SESSION['user'] = $user['utilisateur'];
            header("Location: index.php");
            exit();
        } else {
            $erreur = "Utilisateur na Mot de passe tsy mety!";
        }
    } else {
        $erreur = "Fenoy avokoa ny champ rehetra!";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; }
        .login-card h2 { margin-top: 0; text-align: center; color: #333; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; color: #666; }
        .input-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #0275d8; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #025aa5; }
        .error { color: red; font-size: 14px; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Connexion</h2>
    
    <?php if(!empty($erreur)): ?>
        <div class="error"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="utilisateur" required autofocus>
        </div>
        <div class="input-group">
            <label>Mot de passe</label>
            <input type="password" name="Mot_de_passe" required>
        </div>
        <button type="submit" name="connexion">Se connecter</button>
    </form>
</div>

</body>
</html>