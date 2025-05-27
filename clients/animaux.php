<?php
session_start();

//empeche qqn d'acceder à la page si il n'est pas passer par la page login
if (isset($_SESSION['u_id'])) {
    include("../connexion.inc.php");
    $u_id = $_SESSION['u_id'];
    $role = $_SESSION['role'];

    // fait en sorte que le code soit executer que si on submit un formulaire
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

} else {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace client</title>
    <link rel="stylesheet" href="./animaux.css">
</head>
<body>
    <header>
        <h1><u>Espace client</u></h1> <form action="" method="post"><input type="submit" name="logout" value='déconnexion'></form>
    </header>
    
    <?php
    $qry = $cnx->prepare("SELECT nom, prenom FROM proprietaire WHERE id_proprio = :id");
    $qry->bindParam(":id", $u_id);
    $qry->execute();
    $nomEntier = $qry->fetch(PDO::FETCH_ASSOC);
    echo '<p>Bonjour, ' . strtoupper($nomEntier['nom']) . " " . $nomEntier['prenom'] . '!</p>';
    ?>
    <h2>Vos animaux:</h2>

    <main class="main">
    
    <div>
    <?php

    $requete = $cnx->prepare("SELECT 
                            nom, 
                            espece, 
                            race, 
                            taille_en_m, 
                            genre, 
                            castre, 
                            poids_en_kg 
                            FROM animaux 
                            WHERE id_proprio = :id");
    
    $requete->bindParam(":id", $u_id);
    $requete->execute();
    echo '<table>';
    echo '<thead>
    <tr>
      <th>Nom</th>
      <th>Espèce</th>
      <th>Race</th>
      <th>Taille (m)</th>
      <th>Genre</th>
      <th>Castré</th>
      <th>Poids (kg)</th>
    </tr>
  </thead>
  <tbody>';
    $animaux = $requete->fetchAll(PDO::FETCH_ASSOC);
    foreach($animaux as $animal):
    ?>
    <tr>
        <td><?= $animal['nom'] ?></td>
        <td><?= $animal['espece'] ?></td>
        <td><?= $animal['race'] ?></td>
        <td><?= $animal['taille_en_m'] ?>m</td>
        <td><?= $animal['genre'] ?></td>
        <td><?php if($animal['castre']) {
            echo 'Oui';
        } else {
            echo 'Non';
        } ?></td>
        <td><?= $animal['poids_en_kg'] ?>kg</td>
    </tr>
    <?php 
    endforeach; 
    echo '</tbody>';
    echo '</table>';
    ?>
    </div>
    
    <h2>Historique et détails de vos visites:</h2>
    <div>
        <?php 
        // affichage des consultations
        $requete = $cnx->prepare("SELECT
                                a.nom,
                                c.date_et_heure,
                                c.anamnese,
                                c.lieu,
                                c.diagnostic,
                                ts.tarif_special AS tarif_spe,
                                t.tarif
                                FROM PROPRIETAIRE prop
                                JOIN Animaux a ON prop.id_proprio = a.id_proprio
                                JOIN CONSULTATION c ON a.idA = c.idA
                                JOIN TARIF t ON c.id_tarif = t.id_tarif
                                LEFT JOIN TARIF_SPECIAL ts ON c.id_tarif_speciale = ts.id_tarif_speciale
                                WHERE prop.id_proprio = :id
                                ORDER BY c.date_et_heure DESC");
        $requete->bindParam(":id", $u_id);
        $requete->execute();

        $histo = $requete->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($histo)) {
            echo '<table>';
            echo '<thead>
                <tr>
                    <th>Animal</th>
                    <th>Consultation du:</th>
                    <th>lieu</th>
                    <th>Observation</th>
                    <th>Diagnostic</th>
                    <th>Tarif</th>
                </tr>
            </thead>
            <tbody>';
            foreach($histo as $consultation):
            ?>
            <tr>
                <td><?= $consultation['nom'] ?></td>
                <td><?= date('d-m-Y H:i', strtotime($consultation['date_et_heure'])) ?></td>
                <td><?= $consultation['lieu'] ?></td>
                <td><?= $consultation['anamnese'] ?></td>
                <td><?= $consultation['diagnostic'] ?></td>
                <td><?php if(isset($consultation['tarif_spe'])) {
                    $prix = $consultation['tarif_spe'];
                    echo "$prix € (tarif spécial)";
                } else {
                    $prix = $consultation['tarif'];
                    echo "$prix €";
                } ?></td>
            </tr>
            <?php 
            endforeach; 
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p> aucune consultations sur vos animaux</p>';
        }
        
        ?>
    </div>
    <h2>Manipulations effectuées sur vos animaux:</h2>
    <div>
        <?php
        
        $requete = $cnx->prepare("SELECT
                                a.nom,
                                c.date_et_heure,
                                m.id_manip,
                                m.duree_en_min AS duree_manip,
                                pr.resume_manip
                                FROM PROPRIETAIRE prop
                                JOIN Animaux a ON prop.id_proprio = a.id_proprio
                                JOIN CONSULTATION c ON a.idA = c.idA
                                JOIN pratiquer pr ON c.id_consultation = pr.id_consultation
                                JOIN MANIPULATION m ON pr.id_manip = m.id_manip
                                WHERE prop.id_proprio = :id
                                ORDER BY c.date_et_heure DESC
                                ");
        $requete->bindParam(":id", $u_id);
        $requete->execute();

        $manipulations = $requete->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($manipulations)) {
            echo '<table>';
            echo '<thead>
                    <tr>
                        <th>Animal</th>
                        <th>Consultation du:</th>
                        <th>durée</th>
                        <th>Resumé</th>
                    </tr>
                </thead>
                <tbody>';
            foreach($manipulations as $manip):
            ?>
            <tr>
                <td><?= $manip['nom'] ?></td>
                <td><?= date('d-m-Y H:i', strtotime($manip['date_et_heure'])) ?></td>
                <td><?= $manip['duree_manip'] ?> min</td>
                <td><?= $manip['resume_manip'] ?></td>
            </tr>
            <?php 
            endforeach; 
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p> aucune manipulations sur vos animaux</p>';
        }
        ?>
    </div>
    <h2>Les traitements de vos animaux:</h2>
    <div>
    <?php

    $requete = $cnx->prepare("SELECT 
                            a.nom,
                            p.frequence_jours,
                            p.dose,
                            p.duree_en_jours,
                            t.produit,
                            t.dilution,
                            c.date_et_heure
                            FROM prescription p
                            JOIN traitement t ON p.id_traitement = t.id_traitement
                            JOIN consultation c ON c.id_consultation = p.id_consultation
                            JOIN animaux a ON a.idA = c.idA
                            JOIN proprietaire prop ON a.id_proprio = prop.id_proprio
                            WHERE prop.id_proprio = :id
                            ORDER BY a.nom, c.date_et_heure DESC;
    ");
    $requete->bindParam(":id", $u_id);
    $requete->execute();

    $prescriptions = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($prescriptions)) {
        echo '<table>';
        echo '<thead>
            <tr>
                <th>Animal</th>
                <th>Consultation de:</th>
                <th>Produit</th>
                <th>Posologie</th>
                <th>Durée</th>
                <th>Dilution</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach($prescriptions as $presc) {
            echo '<tr>
                <td>'. $presc['nom'] .'</td>
                <td>'.date('d-m-Y', strtotime($presc['date_et_heure'])).'</td>
                <td>'.$presc['produit'].'</td>
                <td>'.$presc['dose'].' ('.$presc['frequence_jours'].'fois /jour)</td>
                <td>'.$presc['duree_en_jours'].' jours</td>
                <td>'.$presc['dilution'] ?? 'Non précisé'.'</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>Aucune prescription trouvée pour vos animaux.</p>';
    }
?>
    </div>
    </main>
</body>
</html>