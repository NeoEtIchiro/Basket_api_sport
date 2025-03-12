<?php
require_once('connexionDB.php');

class GestionFeuille {
    private $conn;

    public function __construct() {
        $db = new ConnexionDB();
        $this->conn = $db->getConnection();
    }

    // Récupérer les joueurs associés à une rencontre
    function getAllJoueurs($id_rencontre) {
        $stmt = $this->conn->prepare("
            SELECT p.*, j.nom, j.prenom 
            FROM participer p
            JOIN joueur j ON p.id_joueur = j.id_joueur
            WHERE p.id_rencontre = ?
        ");
        $stmt->execute([$id_rencontre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un joueur à une rencontre avec un poste et un statut
    function addJoueur($data) {
        $stmt = $this->conn->prepare("INSERT INTO participer (id_rencontre, id_joueur, poste, titulaire) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['id_rencontre'], $data['id_joueur'], $data['poste'], $data['titulaire']]);
        return $stmt->rowCount() > 0;
    }

    // Mettre à jour le poste et le statut d'un joueur dans une rencontre
    function updateJoueur($data) {
        $stmt = $this->conn->prepare("
            UPDATE participer 
            SET poste = ?, titulaire = ?, note = ?
            WHERE id_rencontre = ? AND id_joueur = ?
        ");
        $stmt->execute([$data['poste'], $data['titulaire'], $data['note'], $data['id_rencontre'], $data['id_joueur']]);
        return $stmt->rowCount() > 0;
    }

    function deleteJoueur($id_joueur, $id_rencontre){
        $stmt = $this->conn->prepare("DELETE FROM participer WHERE id_joueur = ? AND id_rencontre = ?");
        $stmt->execute([$id_joueur, $id_rencontre]);
        return $stmt->rowCount() > 0;
    }
}
?>