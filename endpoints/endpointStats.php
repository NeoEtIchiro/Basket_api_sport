<?php 
require_once '../gestions/gestionStats.php';
require_once '../gestions/checkToken.php';

if(!checkToken()){
    deliver_response(401, "Token invalide ou manquant", false);
    exit;
}

$gestionStats = new GestionStats();
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method){
    case "GET" :
        // Si un paramètre 'type' est fourni, on retourne les stats globales ou par joueur
        if(isset($_GET['type'])){
            $type = htmlspecialchars($_GET['type']);
            if($type == "global"){
                $stats = $gestionStats->calculateGlobalStats();
                deliver_response(200, "Statistiques globales", $stats);
            }
            else if($type == "player"){
                $stats = $gestionStats->calculatePlayerStats();
                deliver_response(200, "Statistiques par joueur", $stats);
            }
            else{
                deliver_response(400, "Type de statistiques inconnu. Utilisez 'global' ou 'player'", null);
            }
        } 
        else {
            deliver_response(200, "Veuillez choisir un type de stat", null);
        }
    break;
    
    default:
        deliver_response(405, "Méthode HTTP non autorisée", null);
}

function deliver_response($status_code, $status_message, $data=null){
    http_response_code($status_code);
    header("Access-Control-Allow-Origin: *");
    header("Content-Type:application/json; charset=utf-8");
    
    $response = [
        'status_code' => $status_code,
        'status_message' => $status_message,
        'data' => $data
    ];
    
    $json_response = json_encode($response);
    if($json_response === false) {
        die('JSON encode error: ' . json_last_error_msg());
    }
    echo $json_response;
}
?>