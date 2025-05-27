<?php
session_start();

// empeche l'acces a la page si on est pas passer par le formulaire dans la page précedente
if (!isset($_SESSION['role']) || !isset($_SESSION['id_animal'])) {
    header("Location: ../index.php");
    exit;
}

include("../connexion.inc.php");
$id_animal = $_SESSION['id_animal'];

//infos animal
$req_animal = $cnx->prepare("SELECT 
                            a.*, 
                            p.nom as proprio_nom, 
                            p.prenom as proprio_prenom 
                            FROM Animaux a
                            JOIN PROPRIETAIRE p ON a.id_proprio = p.id_proprio
                            WHERE a.ida = :id_animal
                            ");
$req_animal->bindParam(":id_animal", $id_animal);
$req_animal->execute();
$animal = $req_animal->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche animal - <?=$animal['nom'] ?></title>
    <link rel="stylesheet" href="./fiche_animal.css">
</head>
<body>
    <a href="./dashboard.php"><button>Retour</button></a>

    <div class="info-section">
        <h1>Fiche de <?= $animal['nom'] ?></h1>
        
        <h2>Informations générales</h2>
        <p><strong>Propriétaire :</strong> <?= $animal['proprio_nom'] ?> <?= $animal['proprio_prenom'] ?></p>
        <p><strong>Espèce :</strong> <?= $animal['espece'] ?></p>
        <p><strong>Race :</strong> <?= $animal['race'] ?></p>
        <p><strong>Genre :</strong> <?= $animal['genre'] ?></p>
        <p><strong>Poids :</strong> <?= $animal['poids_en_kg'] ?> kg</p>
        <p><strong>Taille :</strong> <?= $animal['taille_en_m'] ?> m</p>
        <p><strong>Castré :</strong> 
        <?php 
            if($animal['castre']) {
                echo 'oui';
            } else {
                echo 'non';
            }?>
        </p>
    </div>

    <div class="info-section">
        <h2>Historique des consultations</h2>
        
        <?php 
        // recup histo des consultations pour l'afficher apres
        $req_consultations = $cnx->prepare("SELECT 
                                            c.*, 
                                            t.type_consultation, 
                                            t.tarif
                                            FROM CONSULTATION c
                                            JOIN TARIF t ON c.id_tarif = t.id_tarif
                                            WHERE c.ida = :id_animal
                                            ORDER BY c.date_et_heure DESC
                                            ");
        $req_consultations->bindParam(":id_animal", $id_animal);
        $req_consultations->execute();
        $consultations = $req_consultations->fetchAll(PDO::FETCH_ASSOC);
        // si il existe des consultations pour l'animal on va chercher le reste des infos
        if (count($consultations) > 0): 
            foreach ($consultations as $consult): 
                // recup des manips pour la consultation $consult
                $req_manip = $cnx->prepare("SELECT m.*, p.resume_manip 
                                           FROM pratiquer p
                                           JOIN MANIPULATION m ON p.id_manip = m.id_manip
                                           WHERE p.id_consultation = :id_consult");
                $req_manip->bindParam(":id_consult", $consult['id_consultation']);
                $req_manip->execute();
                $manipulations = $req_manip->fetchAll(PDO::FETCH_ASSOC);

                // recup des préscriptions
                $req_prescriptions = $cnx->prepare("SELECT p.*, t.produit, t.dilution 
                                                   FROM PRESCRIPTION p
                                                   JOIN TRAITEMENT t ON p.id_traitement = t.id_traitement
                                                   WHERE p.id_consultation = :id_consult");
                $req_prescriptions->bindParam(":id_consult", $consult['id_consultation']);
                $req_prescriptions->execute();
                $prescriptions = $req_prescriptions->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <div class="consultation">
                <h3>Consultation du <?= date('d-m-Y H:i', strtotime($consult['date_et_heure'])) ?></h3>
                <p><strong>Type :</strong> <?= $consult['type_consultation'] ?></p>
                <p><strong>Diagnostic :</strong> <?= $consult['diagnostic'] ?></p>
                <p><strong>Durée :</strong> <?= $consult['duree_en_min'] ?> min</p>
                <p><strong>Tarif :</strong> <?= $consult['tarif'] ?> €</p>
                <p><strong>Anamnèse :</strong> <?= $consult['anamnese'] ?></p>
                
                <p><strong>consultation précedente :</strong> 
                <?php
                // recupere la date de la consultation précedente si il y'en a une
                if(!empty($consult['id_consultation_1'])){
                    $precedent = $cnx->prepare("SELECT date_et_heure from consultation WHERE id_consultation = :id");
                    $precedent->bindParam(":id", $consult['id_consultation_1']);
                    $precedent->execute();
                    $a= $precedent->fetch(PDO::FETCH_ASSOC);
                    echo date('d-m-Y H:i', strtotime($a['date_et_heure']));
                } else {
                    echo 'pas de consultation précédente';
                }
                ?>
                </p>

                <div class="consultation-details">
                    
                    <?php // manipulations 
                    if (!empty($manipulations)): ?>
                        <div class="detail-section">
                            <div class="detail-title">Manipulations effectuées :</div>
                            <ul>
                                <?php foreach ($manipulations as $manip): ?>
                                    <li>
                                        <?= $manip['resume_manip'] ?> 
                                        (Durée: <?= $manip['duree_en_min'] ?> min)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                
                    <?php // prescriptions 
                    if (!empty($prescriptions)): ?>
                        <div class="detail-section">
                            <div class="detail-title">Prescriptions :</div>
                            <ul>
                                <?php foreach ($prescriptions as $presc): ?>
                                    <li>
                                        <strong><?= $presc['produit'] ?></strong> - 
                                        Posologie: <?= $presc['dose'] ?> 
                                        (<?= $presc['frequence_jours'] ?>fois/jour pendant <?= $presc['duree_en_jours'] ?> jours)
                                        <?php if (!empty($presc['dilution'])): ?>
                                            - Dilution: <?= $presc['dilution'] ?>
                                        <?php else: ?>
                                            X
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
        <?php 
            endforeach; 
            // si il n'existe pas de consultation pour l'animal on ne fait rien
        else: 
        ?>
            <p>Aucune consultation enregistrée pour cet animal.</p>
        <?php endif; ?>
    </div>
</body>
</html>