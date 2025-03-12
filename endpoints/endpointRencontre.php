<?php 
require_once '../gestions/gestionRencontre.php';
require_once '../gestions/checkToken.php';

if(!checkToken()){
    deliver_response(401, "Token invalide ou manquant", false);
    exit;
}

/// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method){
    case "GET" :
        $gestionRencontre = new GestionRencontre();

        //Récupération des données dans l’URL
        if(isset($_GET['id']))
        {
            // Si on donne un id c'est qu'on veut savoir si le joueur a participer à une rencontre
            $id=htmlspecialchars($_GET['id']);

            $rencontre = $gestionRencontre->getRencontre($id);
            if($rencontre)
                deliver_response(200, "Rencontre avec l'id : " . $id, $rencontre);
            else
                deliver_response(418, "Aucune renctre avec l'id : " . $id, null);
            break;
        }
        else if(isset($_GET['avenir']))
        {
            deliver_response(200, "Rencontres à venir", $gestionRencontre->getRencontresAVenir());
            break;
        }
        else if(isset($_GET['passees']))
        {
            deliver_response(200, "Rencontres passées", $gestionRencontre->getRencontresPassees());
            break;
        }

        deliver_response(200, "Toutes les rencontres", $gestionRencontre->getAllRencontres());
    break;
    case "POST" :
        $gestionRencontre = new GestionRencontre();

        // Récupération des données dans le corps
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 
        /*Reçoit du json et renvoi une adaptation exploitable en php. Le paramètre true impose un tableau en retour
        et non un objet.*/
        if(!isset($data['date_rencontre']) || !isset($data['lieu']) || !isset($data['adversaire'])){
            deliver_response(400, "Erreur de données, veuiller entrer une rencontre au format JSON", null);
            return;
        }

        deliver_response(201, "Création d'une nouvelle rencontre", $gestionRencontre->addRencontre($data));

        //Traitement des données
    break;
    case "PUT" :
        $gestionRencontre = new GestionRencontre();
        
        // Récupération des données dans le corps
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true); 
        /* Le JSON doit contenir :
           - id
           - date_rencontre
           - adversaire
           - lieu
           - resultat
        */
        if(!isset($data['id']) || !isset($data['date_rencontre']) ||
           !isset($data['adversaire']) || !isset($data['lieu']) || !isset($data['resultat'])){
            deliver_response(400, "Erreur de données, veuillez entrer une rencontre au format JSON", null);
            return;
        }
    
        if(!$gestionRencontre->updateRencontre($data))
            deliver_response(404, "Aucune rencontre avec l'id : " . $data['id'], false);
        else
            deliver_response(200, "Modification de la rencontre avec l'id : " . $data['id'], true);
    break;
    case "DELETE":
        $gestionRencontre = new GestionRencontre();
        
        // Récupération des données dans l’URL
        if(!isset($_GET['id'])){
            deliver_response(400, "Erreur de données, veuillez entrer un 'id' dans l'URL", null);
            return;
        }
        
        $id = htmlspecialchars($_GET['id']);
        
        if(!$gestionRencontre->deleteRencontre($id))
            deliver_response(404, "Aucune rencontre avec l'id : " . $id, false);
        else
            deliver_response(200, "Suppression de la rencontre avec l'id : " . $id, true);
    break;
}

/// Envoi de la réponse au Client
function deliver_response($status_code, $status_message, $data=null){
    /// Paramétrage de l'entête HTTP
    http_response_code($status_code); //Utilise un message standardisé en fonction du code HTTP

    header("Access-Control-Allow-Origin: *");
    //header("HTTP/1.1 $status_code $status_message"); //Permet de personnaliser le message associé au code HTTP
    header("Content-Type:application/json; charset=utf-8");//Indique au client le format de la réponse
    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    /// Mapping de la réponse au format JSON
    $json_response = json_encode($response);

    if($json_response===false)
        die('json encode ERROR : '.json_last_error_msg());

    /// Affichage de la réponse (Retourné au client)
    echo $json_response;
}

?>