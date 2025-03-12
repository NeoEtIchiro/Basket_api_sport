<?php

function checkToken(){
    // Récupération des headers HTTP
    $headers = getallheaders();
    if(!isset($headers['Authorization'])){
        return false;
    }
    
    $authHeader = $headers['Authorization'];
    // Vérifier que le header est bien au format "Bearer <token>"
    if(strpos($authHeader, "Bearer ") !== 0){
        return false;
    }
    
    $token = trim(substr($authHeader, 7));
    
    // URL de votre endpoint d'authentification (adaptée à votre environnement)
    $authUrl = "http://localhost/ProjetSport/Basket_api_auth/endPointAuth.php?token=" . urlencode($token);
    
    // Effectuer la requête GET vers l'API d'authentification
    $response = @file_get_contents($authUrl);
    if($response === false){
        return false;
    }
    
    $result = json_decode($response, true);
    // Si l'API d'authentification répond avec un code 200, le token est valide.
    if(isset($result['status_code']) && $result['status_code'] == 200){
        return true;
    }
    
    return false;
}

?>