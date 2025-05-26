<?php
include("connexion.inc.php");
$error = '';

try {
    // On vérifie les identifiants
    $verif = $cnx->prepare("SELECT id_auth, role FROM compte WHERE identifiant = :id AND mdp = :mdp");
    $verif->bindParam(':id', $id);
    $verif->bindParam(':mdp', $mdp);
    $verif->execute();
    
    if ($verif->rowCount() > 0) {
        $row = $verif->fetch();

        // Vétérinaire
        if ($row['role'] === 'veterinaire'){
            $_SESSION['role'] = $row['role'];
            if(!empty($_SESSION['role'])) {
                header("Location: assistant_et_veto/dashboard.php");
                exit;
            }
        }

        // Assistant
        if ($row['role'] === 'assistant'){
            $_SESSION['role'] = $row['role'];
            if(!empty($_SESSION['role'])) {
                header("Location: assistant_et_veto/dashboard.php");
                exit;
            }
        }

        // Propriétaire
        if ($row['role'] === 'proprietaire'){
            $recup_user = $cnx->prepare("SELECT id_proprio FROM proprietaire WHERE id_auth = :id_auth");
            $recup_user->bindParam(':id_auth', $row['id_auth']);
            $recup_user->execute();
            
            $result = $recup_user->fetch(PDO::FETCH_ASSOC);

            $_SESSION['u_id'] = $result['id_proprio'];

            header("Location: clients/animaux.php");
            exit;
        }
    } else {
        $error = 'Identifiant ou mot de passe incorrect';
    }
} catch (PDOException $e) {
    $error = 'erreur de connexion';
}

$_SESSION['login_error'] = $error;
header("Location: login.php");
exit;