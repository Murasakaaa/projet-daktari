<?php
session_start();

// si on est pas passé par le form de la page dashboard on ne peu pas rentrer ici
if (!isset($_SESSION['role']) || !isset($_SESSION['client_id'])) {
    header("Location: ../index.php");
    exit;
}

include("../connexion.inc.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche client</title>
    <link rel="stylesheet" href="./clients.css">
</head>
<body>
    <h1>Fiche client</h1>
    <a href="dashboard.php"><button>Retour au tableau de bord</button></a>

    <div class="info-box">
        <h2>Informations client</h2>
        <?php 
        // infos proprios
        $req_client = $cnx->prepare("SELECT
                                    p.*, 
                                    COUNT(a.idA) as nb_animaux
                                    FROM PROPRIETAIRE p
                                    LEFT JOIN Animaux a ON p.id_proprio = a.id_proprio
                                    WHERE p.id_proprio = :id
                                    GROUP BY p.id_proprio
                                    ");
        $req_client->bindParam(":id", $_SESSION['client_id']);
        $req_client->execute();
        $client = $req_client->fetch(PDO::FETCH_ASSOC); ?>

        <p><strong>Nom :</strong> <?= $client['nom'] ?></p>
        <p><strong>Prénom :</strong> <?= $client['prenom'] ?></p>
        <p><strong>Adresse :</strong> <?= $client['adresse'] ?></p>
        <p><strong>Téléphone :</strong> <?= $client['telephone'] ?></p>
        <p><strong>Type :</strong> <?= $client['type_professionnel'] ?></p>
        <p><strong>Nombre d'animaux :</strong> <?= $client['nb_animaux'] ?></p>
        <p><strong>IBAN :</strong> <?= $client['iban'] ?></p>
    </div>

    <div class="info-box">
        <h2>Animaux</h2>
        <?php 
        //animaux du client
        $req_animaux = $cnx->prepare("SELECT
                                    * 
                                    FROM Animaux 
                                    WHERE id_proprio = :id
                                    ORDER BY nom
                                    ");
        $req_animaux->bindParam(":id", $_SESSION['client_id']);
        $req_animaux->execute();
        $animaux = $req_animaux->fetchAll(PDO::FETCH_ASSOC);

        if (count($animaux) > 0): 
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Espèce</th>
                        <th>Race</th>
                        <th>Genre</th>
                        <th>Poids (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animaux as $animal): ?>
                        <tr>
                            <td><?= $animal['nom'] ?></td>
                            <td><?= $animal['espece'] ?></td>
                            <td><?= $animal['race'] ?></td>
                            <td><?= $animal['genre'] ?></td>
                            <td><?= $animal['poids_en_kg'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun animal enregistré pour ce client.</p>
        <?php endif; ?>
    </div>
</body>
</html>