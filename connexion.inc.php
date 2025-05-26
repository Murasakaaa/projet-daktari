<?php

$user =  "postgres";
$pass =  "azerty";

try {
    // connection pour verifier les id
    $cnx = new PDO("pgsql:host=localhost;dbname=projet_daktari", $user, $pass); 
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
}
catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée ";
    echo $e;
}

?>