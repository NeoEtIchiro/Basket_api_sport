<?php
require_once('connexionDB.php');

class GestionJoueur {
    private $conn;

    public function __construct() {
        $db = new ConnexionDB();
        $this->conn = $db->getConnection();
    }

    // Fonction pour récupérer tous les joueurs
    function getAllJoueurs() {
        $stmt = $this->conn->prepare("SELECT * FROM joueur");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addJoueur($data) {
        $stmt = $this->conn->prepare("INSERT INTO joueur (nom, prenom, licence, dateNaissance, taille, poids, commentaire, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['nom'], $data['prenom'], $data['numero_licence'], $data['date_naissance'], $data['taille'], $data['poids'], $data['commentaire'], $data['statut']]);
    }
    
    // Fonction pour mettre à jour le commentaire et le statut d'un joueur
    public function updateJoueur($data) {
        $stmt = $this->conn->prepare("UPDATE joueur 
                                      SET nom = ?, prenom = ?, licence = ?, dateNaissance = ?, taille = ?, poids = ?, commentaire = ?, statut = ?
                                      WHERE id_joueur = ?");
        $stmt->execute([$data['nom'], $data['prenom'], $data['numero_licence'], $data['date_naissance'], $data['taille'], $data['poids'], $data['commentaire'], $data['statut'], $data['id']]);
        return $stmt->rowCount() > 0; // Retourne true si au moins une ligne a été modifiée
    }
    
    public function deleteJoueur($id_joueur) {
        $stmt = $this->conn->prepare("DELETE FROM participer WHERE id_joueur = ?");
        $stmt->execute([$id_joueur]);
    
        $stmt = $this->conn->prepare("DELETE FROM joueur WHERE id_joueur = ?");
        $stmt->execute([$id_joueur]);
        return $stmt->rowCount() > 0; // Retourne true si au moins une ligne a été supprimée
    }

    public function hasParticipatedInRencontres($id_joueur) {
        $query = "SELECT COUNT(*) as count FROM participer WHERE id_joueur = :id_joueur";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_joueur' => $id_joueur]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0; // Retourne true si le joueur a participé à au moins une rencontre
    }
}
?>