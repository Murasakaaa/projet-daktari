<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit();
}
include("../connexion.inc.php");


$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $qry = "INSERT INTO CONSULTATION (date_et_heure, duree_en_min, anamnese, lieu, diagnostic, id_tarif, idA) 
                  VALUES (:date_et_heure, :duree, :anamnese, :lieu, :diagnostic, :id_tarif, :idA)";
        
        $stmt = $cnx->prepare($qry);
        $stmt->bindParam(':date_et_heure', $_POST['date_consultation']);
        $stmt->bindParam(':duree', $_POST['duree']);
        $stmt->bindParam(':anamnese', $_POST['anamnese']);
        $stmt->bindParam(':lieu', $_POST['lieu']);
        $stmt->bindParam(':diagnostic', $_POST['diagnostic']);
        $stmt->bindParam(':id_tarif', $_POST['id_tarif']);
        $stmt->bindParam(':idA', $_POST['id_animal']);
        
        $stmt->execute();
        
        $message = "Consultation enregistrée avec succès!";
        
        // si c'est un vétérinaire, on propose d'ajouter des prescriptions
        if ($_SESSION['role'] === 'veterinaire') {
            // à faire
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

// récup des données pour les menus déroulant
$id_animal_session = $_SESSION['id_animal'];

// recuperation des infos de l'animal
$stmt = $cnx->prepare("SELECT ida, nom, espece, race FROM Animaux WHERE ida = :id");
$stmt->bindParam(':id', $id_animal_session);
$stmt->execute();
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

// recuperation des tarifs
$tarifs = $cnx->prepare("SELECT id_tarif, type_consultation, lieu, tarif FROM TARIF WHERE date_fin IS NULL OR date_fin >= CURRENT_DATE");
$tarifs->execute();
$tarifs = $tarifs->fetchAll(PDO::FETCH_ASSOC)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Consultation</title>
    <link rel="stylesheet" href="consultation.css">
</head>
<body>
    <div class="container">
        <a href="./fiche_animal.php"><button>Retour</button></a>
        <h1>Nouvelle Consultation</h1>
        
        <!--affichage message d'erreur ou succes-->
        <?php if ($message): ?>
            <p class="success"><?= $message ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Animal :</label>
                <p><?= $animal['nom']?> (<?= $animal['espece'] ?>/<?= $animal['race'] ?>)</p>
                <input type="hidden" name="id_animal" value="<?= $animal['ida']?>">
            </div>

            <div class="form-group">
                <label for="date_consultation">Date et heure:</label>
                <input type="datetime-local" id="date_consultation" name="date_consultation" required>
            </div>
            
            <div class="form-group">
                <label for="duree">Durée (minutes):</label>
                <input type="number" id="duree" name="duree" min="5" required>
            </div>
            
            <div class="form-group">
                <label for="lieu">Lieu:</label>
                <select id="lieu" name="lieu" required>
                    <option value="cabinet">Cabinet</option>
                    <option value="hors_cabinet">Hors cabinet</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_tarif">Type de consultation:</label>
                <select id="id_tarif" name="id_tarif" required>
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($tarifs as $tarif): ?>
                        <option value="<?= $tarif['id_tarif'] ?>">
                            <?= $tarif['type_consultation'] ?> (<?= $tarif['lieu'] ?>) - <?= $tarif['tarif'] ?>€
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="anamnese">Anamnèse:</label>
                <textarea id="anamnese" name="anamnese" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="diagnostic">Diagnostic:</label>
                <input type="text" id="diagnostic" name="diagnostic" required>
            </div>
            
            <button type="submit" class="btn">Enregistrer la consultation</button>
        </form>
    </div>
</body>
</html>