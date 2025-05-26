<?php
session_start();

// Vérification des droits d'accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'veterinaire') {
    header("Location: ../connexion/login.php");
    exit;
}

include("../connexion.inc.php");

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cnx->beginTransaction();

        // ajout manipulation
        if (isset($_POST['ajout_manip'])) {

            $verify = $cnx->prepare("SELECT * FROM MANIPULATION WHERE id_manip = :id");
            $verify->bindParam(":id", $_POST['id_manip']);
            $verify->execute();
            $v = $verify->fetch(PDO::FETCH_ASSOC);

            if (count($v)>0){
                throw 'la manip existe déjà';
            }


            $stmt = $cnx->prepare("INSERT INTO MANIPULATION (id_manip, duree_en_min) VALUES (:id_manip, :duree)");
            $stmt->bindParam(':id_manip', $_POST['id_manip']);
            $stmt->bindParam(':duree', $_POST['duree_en_min']);
            $stmt->execute();
        }

        // ajout traitement
        if (isset($_POST['ajout_traitement'])) {
            $stmt = $cnx->prepare("INSERT INTO TRAITEMENT (produit, dilution) VALUES (:produit, :dilution)");
            $stmt->bindParam(':produit', $_POST['produit']);
            $stmt->bindParam(':dilution', $_POST['dilution']);
            $stmt->execute();
        }

        // ajout tarif
        if (isset($_POST['ajout_tarif'])) {
            $stmt = $cnx->prepare("INSERT INTO TARIF (type_consultation, lieu, tarif, date_debut) VALUES (:type, :lieu, :tarif, :date_debut)");
            $stmt->bindParam(':type', $_POST['type_consultation']);
            $stmt->bindParam(':lieu', $_POST['lieu']);
            $stmt->bindParam(':tarif', $_POST['tarif']);
            $stmt->bindParam(':date_debut', $_POST['date_debut']);
            $stmt->execute();
        }

        // ajout tarif spécial
        if (isset($_POST['ajout_tarif_special'])) {
            $stmt = $cnx->prepare("INSERT INTO TARIF_SPECIAL (tarif_special, motif) VALUES (:tarif_special, :motif)");
            $stmt->bindParam(':tarif_special', $_POST['tarif_special']);
            $stmt->bindParam(':motif', $_POST['motif']);
            $stmt->execute();
        }

        $cnx->commit();
        $message = "Opération effectuée avec succès";
    } catch (PDOException $e) {
        $cnx->rollBack();
        $erreur = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion - Cabinet Vétérinaire</title>
    <link rel="stylesheet" href="./gestion.css">
</head>
<body>
    <h1>Gestion du cabinet</h1>
    
    <?php if (isset($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>
    
    <?php if (isset($erreur)): ?>
        <p class="error"><?= $erreur ?></p>
    <?php endif; ?>

    
    <div class="box">
        <h2>Ajouter une manipulation</h2>
        <form method="post">
            <label>ID Manipulation: <input type="text" name="id_manip" required></label>
            <label>Durée (minutes): <input type="number" name="duree_en_min" required></label>
            <button type="submit" name="ajout_manip">Ajouter</button>
        </form>
    </div>

    <div class="box">
        <h2>Ajouter un traitement</h2>
        <form method="post">
            <label>Produit: <input type="text" name="produit" required></label>
            <label>Dilution: <input type="text" name="dilution"></label>
            <button type="submit" name="ajout_traitement">Ajouter</button>
        </form>
    </div>

    <div class="box">
        <h2>Ajouter un tarif</h2>
        <form method="post">
            <label>Type de consultation: 
                <input type="text" name="type_consultation" required>
            </label>
            <label>Lieu:
                <select name="lieu" required>
                    <option value="cabinet">Cabinet</option>
                    <option value="hors_cabinet">Domicile</option>
                </select>
            </label>
            <label>Tarif (€): <input type="number" step="0.01" name="tarif" required></label>
            <label>Date d'effet: <input type="date" name="date_debut" required></label>
            <button type="submit" name="ajout_tarif">Ajouter</button>
        </form>
    </div>

    <div class="box">
        <h2>Ajouter un tarif spécial</h2>
        <form method="post">
            <label>Tarif spécial (en €): <input type="number" step="0.01" name="tarif_special" required></label>
            <label>Motif: <textarea name="motif" required></textarea></label>
            <button type="submit" name="ajout_tarif_special">Ajouter</button>
        </form>
    </div>
</body>
</html>