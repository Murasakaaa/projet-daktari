<?php
session_start();
if (isset($_SESSION['login_error'])){
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["identifiant"];
    $mdp = $_POST["mdp"];
    include("redirection.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./login.css">
</head>
<body>
    <h1>Bonjour et bienvenue.</h1>
    <?php
    if (!empty($msg)){
        echo "<p>$msg</p>";
    }
    ?>
    <div class="container">
        <form action="" method="post">
            <input type="text" name="identifiant" placeholder="Identifiant" required>
            <input type="password" name='mdp' placeholder="Mot de passe" required>
            <input type="submit" name="ok" value="Se connecter">
        </form>
    </div>
    <?php 
        if(!empty($error)){
            echo '<p>' . $error . '</p>';
        }
        ?>
</body>
</html>