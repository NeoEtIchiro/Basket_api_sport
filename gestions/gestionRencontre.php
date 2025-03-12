<?php
require_once('connexionDB.php');

class GestionJoueur {
    private $conn;

    public function __construct() {
        $db = new ConnexionDB();
        $this->conn = $db->getConnection();
    }

    function getAllRencontres() {
        $stmt = $pdo->prepare("
            SELECT * FROM rencontre
            ORDER BY date_rencontre DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fonction pour récupérer tous les rencontres à venir
    function getRencontresAVenir() {
        $stmt = $pdo->prepare("
            SELECT * FROM rencontre 
            WHERE date_rencontre >= NOW()
            ORDER BY date_rencontre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les rencontres passées
    function getRencontresPassees() {
        $stmt = $pdo->prepare("
            SELECT * FROM rencontre 
            WHERE date_rencontre < NOW()
            ORDER BY date_rencontre DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une rencontre spécifique
    function getRencontreById($id_rencontre) {
        $stmt = $pdo->prepare("SELECT * FROM rencontre WHERE id_rencontre = ?");
        $stmt->execute([$id_rencontre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Fonction pour ajouter un rencontre avec les champs supplémentaires
    function addRencontre($data) {
        $stmt = $pdo->prepare("INSERT INTO rencontre (date_rencontre, lieu, adversaire, resultat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['date_rencontre'], $data['lieu'], $data['adversaire'], '0-0']);
        return $stmt->rowCount() > 0;
    }
    
    // Mettre à jour les informations d'une rencontre
    function updateRencontre($data) {
        $stmt = $pdo->prepare("
            UPDATE rencontre 
            SET date_rencontre = ?, adversaire = ?, lieu = ?, resultat = ?
            WHERE id_rencontre = ?
        ");
        $stmt->execute([
            $data['date_rencontre'],
            $data['adversaire'],
            $data['lieu'],
            $data['resultat'],
            $data['id_rencontre']
        ]);
        return $stmt->rowCount() > 0;
    }
    
    function deleteRencontre($id_rencontre) {
        $stmt = $pdo->prepare("DELETE FROM rencontre WHERE id_rencontre = ?");
        $stmt->execute([$id_rencontre]);
        return $stmt->rowCount() > 0;
    }
}
?>