<?php
require_once('connexionDB.php');

class GestionRencontre {
    private $conn;

    public function __construct() {
        $db = new ConnexionDB();
        $this->conn = $db->getConnection();
    }

    function getAllRencontres() {
        $stmt = $this->conn->prepare("
            SELECT * FROM rencontre
            ORDER BY date_rencontre DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fonction pour récupérer tous les rencontres à venir
    function getRencontresAVenir() {
        $stmt = $this->conn->prepare("
            SELECT * FROM rencontre 
            WHERE date_rencontre >= NOW()
            ORDER BY date_rencontre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les rencontres passées
    function getRencontresPassees() {
        $stmt = $this->conn->prepare("
            SELECT * FROM rencontre 
            WHERE date_rencontre < NOW()
            ORDER BY date_rencontre DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une rencontre spécifique
    function getRencontre($id) {
        $stmt = $this->conn->prepare("SELECT * FROM rencontre WHERE id_rencontre = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Fonction pour ajouter un rencontre avec les champs supplémentaires
    function addRencontre($data) {
        $stmt = $this->conn->prepare("INSERT INTO rencontre (date_rencontre, lieu, adversaire, resultat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['date_rencontre'], $data['lieu'], $data['adversaire'], '0-0']);
        return $stmt->rowCount() > 0;
    }
    
    // Mettre à jour les informations d'une rencontre
    function updateRencontre($data) {
        $stmt = $this->conn->prepare("
            UPDATE rencontre 
            SET date_rencontre = ?, adversaire = ?, lieu = ?, resultat = ?
            WHERE id_rencontre = ?
        ");
        $stmt->execute([
            $data['date_rencontre'],
            $data['adversaire'],
            $data['lieu'],
            $data['resultat'],
            $data['id']
        ]);
        return $stmt->rowCount() > 0;
    }
    
    function deleteRencontre($id) {
        $stmt = $this->conn->prepare("DELETE FROM rencontre WHERE id_rencontre = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
?>