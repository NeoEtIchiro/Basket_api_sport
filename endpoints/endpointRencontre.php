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
        $gestionJoueur = new GestionJoueur();

        //Récupération des données dans l’URL
        if(isset($_GET['id']))
        {
            // Si on donne un id c'est qu'on veut savoir si le joueur a participer à une rencontre
            $id=htmlspecialchars($_GET['id']);

            if($gestionJoueur->hasParticipatedInRencontres($id))
                deliver_response(200, "Le joueur a au moins un match, id : " . $id, true);
            else
                deliver_response(418, "Cet id n'a aucun match : " . $id, false);

            break;
        }

        deliver_response(200, "Toutes les rencontres", $gestionJoueur->getAllRencontres());
    break;
    case "POST" :
        $gestionJoueur = new GestionJoueur();

        // Récupération des données dans le corps
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 
        /*Reçoit du json et renvoi une adaptation exploitable en php. Le paramètre true impose un tableau en retour
        et non un objet.*/
        if(!isset($data['nom']) || !isset($data['prenom']) || !isset($data['numero_licence']) || !isset($data['date_naissance']) || !isset($data['taille']) || !isset($data['poids']) || !isset($data['commentaire']) || !isset($data['statut'])){
            deliver_response(400, "Erreur de données, veuiller entrer un joueur au format JSON", null);
            return;
        }

        deliver_response(201, "Création d'un nouveau joueur", $gestionJoueur->addJoueur($data));

        //Traitement des données
    break;
    case "PUT" :
        $gestionJoueur = new GestionJoueur();
        
        // Récupération des données dans le corps
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 
        /*Reçoit du json et renvoi une adaptation exploitable en php. Le paramètre true impose un tableau en retour
        et non un objet.*/
        if(!isset($data['id']) || !isset($data['nom']) || !isset($data['prenom']) || !isset($data['numero_licence']) || !isset($data['date_naissance']) || !isset($data['taille']) || !isset($data['poids']) || !isset($data['commentaire']) || !isset($data['statut'])){
            deliver_response(400, "Erreur de données, veuiller entrer un joueur au format JSON", null);
            return;
        }

        if(!$gestionJoueur->updateJoueur($data))
            deliver_response(404, "Aucun joueur avec l'id : " . $data['id'], false);
        else
            deliver_response(200, "Modification du joueur avec l'id : " . $data['id'], true);

        //Traitement des données
    break;
    case "DELETE":
        $gestionJoueur = new GestionJoueur();
        
        //Récupération des données dans l’URL
        if(!isset($_GET['id'])){
            deliver_response(400, "Erreur de données, veuillez entrer un 'id' dans l'URL", null);
            return;
        }

        $id=htmlspecialchars($_GET['id']);

        if(!$gestionJoueur->deleteJoueur($id))
            deliver_response(404, "Aucun joueur avec l'id : " . $id, false);
        else
            deliver_response(200, "Suppression du joueur avec l'id : " . $id, true);
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