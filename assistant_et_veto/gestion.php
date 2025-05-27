<?php
session_start();

// Vérification des droits d'accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'veterinaire') {
    header("Location: ../index.php");
    exit;
}

include("../connexion.inc.php");

// fait en sorte que le code soit executer que si on submit un formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cnx->beginTransaction();

        // ajout manipulation
        if (isset($_POST['ajout_manip'])) {

            // verif si la manip n'existe pas déjà
            $verify = $cnx->prepare("SELECT * FROM MANIPULATION WHERE id_manip = :id");
            $verify->bindParam(":id", $_POST['id_manip']);
            $verify->execute();
            
            // si une manip a la meme clé primaire on renvoi une erreur
            if ($verify->rowCount()>0){
                throw new Exception("la manip existe déjà");
            }

            $qry = $cnx->prepare("INSERT INTO MANIPULATION (id_manip, duree_en_min) VALUES (:id_manip, :duree)");
            $qry->bindParam(':id_manip', $_POST['id_manip']);
            $qry->bindParam(':duree', $_POST['duree_en_min']);
            $qry->execute();
        }

        // ajout traitement
        if (isset($_POST['ajout_traitement'])) {
            $qry = $cnx->prepare("INSERT INTO TRAITEMENT (produit, dilution) VALUES (:produit, :dilution)");
            $qry->bindParam(':produit', $_POST['produit']);
            $qry->bindParam(':dilution', $_POST['dilution']);
            $qry->execute();
        }

        // ajout tarif
        if (isset($_POST['ajout_tarif'])) {
            $qry = $cnx->prepare("INSERT INTO TARIF (type_consultation, lieu, tarif, date_debut) VALUES (:type, :lieu, :tarif, :date_debut)");
            $qry->bindParam(':type', $_POST['type_consultation']);
            $qry->bindParam(':lieu', $_POST['lieu']);
            $qry->bindParam(':tarif', $_POST['tarif']);
            $qry->bindParam(':date_debut', $_POST['date_debut']);
            $qry->execute();
        }

        // ajout tarif spécial
        if (isset($_POST['ajout_tarif_special'])) {
            $qry = $cnx->prepare("INSERT INTO TARIF_SPECIAL (tarif_special, motif) VALUES (:tarif_special, :motif)");
            $qry->bindParam(':tarif_special', $_POST['tarif_special']);
            $qry->bindParam(':motif', $_POST['motif']);
            $qry->execute();
        }

        $cnx->commit();
        $_SESSION['message'] = "Opération effectuée avec succès";

    } catch (Exception $e) {
        $cnx->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    //permet d'actualiser la page apres l'insertion
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
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
    
    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?= $_SESSION['message'] ?></p>
    <?php 
    unset($_SESSION['message']);
    endif; 
    ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?= $_SESSION['error'] ?></p>
    <?php 
    unset($_SESSION['error']);
    endif; 
    ?>
    
    <div class="box">
        <h2>Ajouter une manipulation</h2>
        <form method="post">
            <label>ID Manipulation: <input type="text" name="id_manip" required></label>
            <label>Durée (minutes): <input type="number" name="duree_en_min" required></label>
            <button type="submit" name="ajout_manip">Ajouter</button>
        </form>
    </div>
    <div class="box">
        <?php 
        $requete_manip = $cnx->prepare("SELECT * FROM MANIPULATION");
        $requete_manip->execute();
        $manips = $requete_manip->fetchAll(PDO::FETCH_ASSOC);
        echo '<table>';
        echo '<thead>
                <tr>
                    <th>Id Manipulation</th>
                    <th>durée</th>
                </tr>
            </thead>
            <tbody>';
    foreach($manips as $manip):
    ?>
    <tr>
        <td><?= $manip['id_manip'] ?></td>
        <td><?= $manip['duree_en_min'] ?>min</td>
    </tr>
    <?php 
    endforeach; 
    echo '</tbody>';
    echo '</table>';
    ?>
    </div>

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
        <?php 
        $requete_tratiement = $cnx->prepare("SELECT * FROM TRAITEMENT");
        $requete_tratiement->execute();
        $traitements = $requete_tratiement->fetchAll(PDO::FETCH_ASSOC);
        echo '<table>';
        echo '<thead>
                <tr>
                    <th>Produit</th>
                    <th>Dilution</th>
                </tr>
            </thead>
            <tbody>';
    foreach($traitements as $t):
    ?>
    <tr>
        <td><?= $t['produit'] ?></td>
        <td>
            <?php
                if(!empty($t['dilution'])) {
                    echo $t['dilution'];
                } else {
                    echo 'X';
                } 
            ?>
        </td>
    </tr>
    <?php 
    endforeach; 
    echo '</tbody>';
    echo '</table>';
    ?>
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
        <?php 
        $requete_tarif = $cnx->prepare("SELECT * FROM TARIF");
        $requete_tarif->execute();
        $tarifs = $requete_tarif->fetchAll(PDO::FETCH_ASSOC);
        echo '<table class="tarif">';
        echo '<thead>
                <tr>
                    <th>Type</th>
                    <th>Lieu</th>
                    <th>date début</th>
                    <th>date fin</th>
                    <th>Tarif</th>
                </tr>
            </thead>
            <tbody>';
    foreach($tarifs as $tarif):
    ?>
    <tr>
        <td><?= $tarif['type_consultation'] ?></td>
        <td><?= $tarif['lieu'] ?></td>
        <td><?= date('d-m-Y' , strtotime($tarif['date_debut'])) ?></td>
        <td>
            <?php
                if(!empty($tarif['date_fin'])) {
                    echo date('d-m-Y' , strtotime($tarif['date_fin']));
                } else {
                    echo 'X';
                } 
            ?>
        </td>
        <td><?= $tarif['tarif'] ?>€</td>
    </tr>
    <?php 
    endforeach; 
    echo '</tbody>';
    echo '</table>';
    ?>
    </div>

    <div class="box">
        <h2>Ajouter un tarif spécial</h2>
        <form method="post">
            <label>Tarif spécial (en €): <input type="number" step="0.01" name="tarif_special" required></label>
            <label>Motif: <textarea name="motif" required></textarea></label>
            <button type="submit" name="ajout_tarif_special">Ajouter</button>
        </form>
    </div>
    <?php 
        $requete_tarif = $cnx->prepare("SELECT * FROM TARIF_SPECIAL");
        $requete_tarif->execute();
        $tarifs_spe = $requete_tarif->fetchAll(PDO::FETCH_ASSOC);
        echo '<table class="tarif">';
        echo '<thead>
                <tr>
                    <th>Tarif</th>
                    <th>Motif</th>
                </tr>
            </thead>
            <tbody>';
    foreach($tarifs_spe as $tarif):
    ?>
    <tr>
        <td><?= $tarif['tarif_special'] ?></td>
        <td><?= $tarif['motif'] ?></td>
    </tr>
    <?php 
    endforeach; 
    echo '</tbody>';
    echo '</table>';
    ?>
</body>
</html>