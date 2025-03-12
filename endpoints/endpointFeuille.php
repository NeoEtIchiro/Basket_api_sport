<?php 
require_once '../gestions/gestionFeuille.php';
require_once '../gestions/checkToken.php';

if(!checkToken()){
    deliver_response(401, "Token invalide ou manquant", false);
    exit;
}

$gestionFeuille = new GestionFeuille();

$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method){
    // Récupérer tous les joueurs associés à une rencontre
    case "GET" :
        if(!isset($_GET['id_rencontre'])){
            deliver_response(400, "Erreur de données, 'id_rencontre' manquant dans l'URL", null);
            return;
        }
        
        $id_rencontre = htmlspecialchars($_GET['id_rencontre']);
        $joueurs = $gestionFeuille->getAllJoueurs($id_rencontre);
        if($joueurs)
            deliver_response(200, "Liste des joueurs pour la rencontre $id_rencontre", $joueurs);
        else
            deliver_response(404, "Aucun joueur trouvé pour la rencontre $id_rencontre", null);
    break;

    // Ajouter un joueur à une rencontre
    case "POST" :
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true); 
        /* Le JSON doit contenir :
           - id_rencontre
           - id_joueur
           - poste
           - titulaire 
        */
        if(!isset($data['id_rencontre']) || !isset($data['id_joueur']) || !isset($data['poste']) || !isset($data['titulaire'])){
            deliver_response(400, "Erreur de données, le JSON doit contenir id_rencontre, id_joueur, poste et titulaire", null);
            return;
        }
        
        if($gestionFeuille->addJoueur($data))
            deliver_response(201, "Le joueur a été ajouté à la rencontre", true);
        else
            deliver_response(500, "Erreur lors de l'ajout du joueur", false);
    break;

    // Mettre à jour le poste, le statut et la note d'un joueur dans une rencontre
    case "PUT" :
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true); 
        /* Le JSON doit contenir :
           - id_rencontre
           - id_joueur
           - poste
           - titulaire
           - note
        */
        if(!isset($data['id_rencontre']) || !isset($data['id_joueur']) || !isset($data['poste']) || !isset($data['titulaire']) || !isset($data['note'])){
            deliver_response(400, "Erreur de données, le JSON doit contenir id_rencontre, id_joueur, poste, titulaire et note", null);
            return;
        }
        
        if($gestionFeuille->updateJoueur($data))
            deliver_response(200, "Mise à jour réussie pour le joueur dans la rencontre", true);
        else
            deliver_response(404, "Mise à jour impossible, vérifiez les identifiants", false);
    break;

    // Supprimer un joueur d'une rencontre
    case "DELETE":
        if(!isset($_GET['id_rencontre']) || !isset($_GET['id_joueur'])){
            deliver_response(400, "Erreur de données, 'id_rencontre' et 'id_joueur' sont requis dans l'URL", null);
            return;
        }
        $id_rencontre = htmlspecialchars($_GET['id_rencontre']);
        $id_joueur = htmlspecialchars($_GET['id_joueur']);
        
        if($gestionFeuille->deleteJoueur($id_joueur, $id_rencontre))
            deliver_response(200, "Le joueur a été supprimé de la rencontre", true);
        else
            deliver_response(404, "Aucun joueur n'a été supprimé, vérifiez les identifiants", false);
    break;

    default:
        deliver_response(405, "Méthode HTTP non autorisée", null);
}

/// Envoi de la réponse au Client
function deliver_response($status_code, $status_message, $data=null){
    http_response_code($status_code);
    header("Access-Control-Allow-Origin: *");
    header("Content-Type:application/json; charset=utf-8");
    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;
    
    $json_response = json_encode($response);
    if($json_response===false)
        die('json encode ERROR : '.json_last_error_msg());
    
    echo $json_response;
}
?>