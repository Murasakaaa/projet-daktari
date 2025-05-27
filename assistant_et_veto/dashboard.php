<?php 
session_start();

// Empêche l'accès sans connexion
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

include("../connexion.inc.php");
$role = $_SESSION['role'];

// on verif que l'animal existe bien pour ce proprietaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id_proprio']) && isset($_POST['nom_animal'])) {
        $id_proprio = $_POST['id_proprio'];
        $nom_animal = trim($_POST['nom_animal']);
        
        
        $req_animal = $cnx->prepare("SELECT 
                                    ida
                                    FROM Animaux 
                                    WHERE id_proprio = :id AND nom = :nom");

        $req_animal->bindParam(":id", $id_proprio);
        $req_animal->bindParam(":nom", $nom_animal);
        $req_animal->execute();
        $animal = $req_animal->fetch(PDO::FETCH_ASSOC);
        
        if ($animal) {
            $_SESSION['id_animal'] = $animal['ida'];
            header("Location: ./fiche_animal.php");
            exit;
        } else {
            $erreur = "Cet animal n'existe pas pour ce propriétaire";
        }
    }
}

// aller vers la page client lorsque le bon bouton est cliqué
if (isset($_POST['client_id'])) {
    $_SESSION['client_id'] = $_POST['client_id'];
    header("Location: ./clients.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace pro</title>
    <link rel="stylesheet" href="./dash.css">
    <style>
        
    </style>
</head>
<body>
    <header>
        <h1><u>Tableau de bord</u></h1>
        <?php if($role === 'veterinaire') {
            echo "<a href='./gestion.php'><button>Gestion</button></a>";
        } ?>
    </header>
    <main>
        <div class="container">
            <!-- Formulaire sélection animal -->
            <div class="selection-box">
                <h2>Sélectionner animal:</h2>
                <?php if (!empty($erreur)) echo '<p style="color: red;">'.$erreur.'</p>'; ?>
                <form action="" method="post">
                    <div>
                        <label>Propriétaire:</label>
                        <select name="id_proprio" required>
                            <option value="">-- Choisir un propriétaire --</option>
                            <?php 
                            $req_proprietaires = $cnx->prepare("SELECT 
                                                            id_proprio, 
                                                            nom, 
                                                            prenom 
                                                            FROM PROPRIETAIRE 
                                                            ORDER BY nom, prenom");
                            $req_proprietaires->execute();
                            $proprietaires = $req_proprietaires->fetchAll(PDO::FETCH_ASSOC);
                            foreach($proprietaires as $proprio): 
                            ?>
                                <option value="<?= $proprio['id_proprio'] ?>">
                                    <?= $proprio['nom'] ?> <?= $proprio['prenom'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Nom de l'animal:</label>
                        <input type="text" name="nom_animal" required>
                    </div>
                    <button type="submit">Continuer vers consultation</button>
                </form>
            </div>

            <!-- Formulaire accès fiche client -->
            <div class="selection-box">
                <h2>Accéder à la fiche client:</h2>
                <form action="" method="post">
                    <div>
                        <label>Propriétaire:</label>
                        <select name="client_id" required>
                            <option value="">-- Choisir un client --</option>
                            <?php foreach($proprietaires as $proprio): ?>
                                <option value="<?= $proprio['id_proprio'] ?>">
                                    <?= $proprio['nom'] ?> <?= $proprio['prenom'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Voir la fiche client</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>