<?php
require_once('connexionDB.php');
require_once('gestionRencontre.php');
require_once('gestionJoueurs.php');
require_once('gestionFeuille.php');

class GestionStats {
    private $gestionRencontre;
    private $gestionJoueur;
    private $gestionFeuille;
    
    public function __construct() {
        $this->gestionRencontre = new GestionRencontre();
        $this->gestionJoueur = new GestionJoueur(); // Note : dans votre fichier, la classe s'appelle "GestionJoueur"
        $this->gestionFeuille  = new GestionFeuille();
    }
    
    // Retourne toutes les rencontres depuis GestionRencontre
    public function getAllRencontres() {
        return $this->gestionRencontre->getAllRencontres();
    }
    
    // Retourne tous les joueurs depuis GestionJoueur
    public function getAllJoueurs() {
        return $this->gestionJoueur->getAllJoueurs();
    }
    
    // Retourne les joueurs d'une rencontre via GestionFeuille
    public function getJoueursByRencontre($idRencontre) {
        return $this->gestionFeuille->getAllJoueurs($idRencontre);
    }
    
    // Calcule les statistiques globales en utilisant les rencontres de GestionRencontre
    public function calculateGlobalStats() {
        $rencontres = $this->getAllRencontres();
        $total_matches = count($rencontres);
        $wins = 0;
        $draws = 0;
        $losses = 0;
    
        foreach ($rencontres as $rencontre) {
            $resultat = explode(' - ', $rencontre['resultat']);
            if (count($resultat) == 2) {
                list($score_nous, $score_adversaire) = $resultat;
                if ($score_nous > $score_adversaire) {
                    $wins++;
                } elseif ($score_nous == $score_adversaire) {
                    $draws++;
                } else {
                    $losses++;
                }
            }
        }
    
        $win_percentage = $total_matches ? ($wins / $total_matches) * 100 : 0;
        $draw_percentage = $total_matches ? ($draws / $total_matches) * 100 : 0;
        $loss_percentage = $total_matches ? ($losses / $total_matches) * 100 : 0;
    
        return [
            'total_matches'  => $total_matches,
            'wins'           => $wins,
            'draws'          => $draws,
            'losses'         => $losses,
            'win_percentage' => $win_percentage,
            'draw_percentage'=> $draw_percentage,
            'loss_percentage'=> $loss_percentage,
        ];
    }
    
    // Calcule les statistiques par joueur en s'appuyant sur les méthodes existantes
    public function calculatePlayerStats() {
        $rencontres = $this->getAllRencontres();
        $joueurs = $this->getAllJoueurs();
        
        $player_stats = [];
        foreach ($joueurs as $joueur) {
            $id = $joueur['id_joueur'];
            $player_stats[$id] = [
                'statut'                  => isset($joueur['statut']) ? $joueur['statut'] : null,
                'postes'                  => [],
                'titularisations'         => 0,
                'remplacements'           => 0,
                'moyenne_evaluations'      => 0,
                'matchs_gagnes'           => 0,
                'total_matchs'            => 0,
                'selections_consecutives' => 0,
            ];
        }
        
        foreach ($rencontres as $rencontre) {
            $resultat = explode(' - ', $rencontre['resultat']);
            if (count($resultat) == 2) {
                list($score_nous, $score_adversaire) = $resultat;
                // Utilisation de la fonction de GestionFeuille pour récupérer les joueurs associés à la rencontre
                $joueurs_rencontre = $this->getJoueursByRencontre($rencontre['id_rencontre']);
                foreach ($joueurs_rencontre as $jr) {
                    $id = $jr['id_joueur'];
                    if (!isset($player_stats[$id])) continue;
                    
                    $player_stats[$id]['total_matchs']++;
                    if ($jr['titulaire']) {
                        $player_stats[$id]['titularisations']++;
                    } else {
                        $player_stats[$id]['remplacements']++;
                    }
                    
                    $player_stats[$id]['moyenne_evaluations'] += $jr['note'];
                    
                    if ($score_nous > $score_adversaire) {
                        $player_stats[$id]['matchs_gagnes']++;
                    }
                    
                    if (!isset($player_stats[$id]['postes'][$jr['poste']])) {
                        $player_stats[$id]['postes'][$jr['poste']] = 0;
                    }
                    $player_stats[$id]['postes'][$jr['poste']]++;
                }
            }
        }
        
        // Finalisation du calcul pour chaque joueur
        foreach ($player_stats as $id_joueur => $stats) {
            if ($stats['total_matchs'] > 0) {
                $player_stats[$id_joueur]['moyenne_evaluations'] /= $stats['total_matchs'];
            }
            $player_stats[$id_joueur]['pourcentage_matchs_gagnes'] = $stats['total_matchs'] ? ($stats['matchs_gagnes'] / $stats['total_matchs']) * 100 : 0;
            arsort($player_stats[$id_joueur]['postes']);
            $player_stats[$id_joueur]['poste_prefere'] = key($player_stats[$id_joueur]['postes']);
        }
        
        // Calcul du nombre de sélections consécutives pour chaque joueur
        foreach ($joueurs as $joueur) {
            $id = $joueur['id_joueur'];
            $selections_consecutives = 0;
            $max_selections_consecutives = 0;
            foreach ($rencontres as $rencontre) {
                $joueurs_rencontre = $this->getJoueursByRencontre($rencontre['id_rencontre']);
                $is_selected = false;
                foreach ($joueurs_rencontre as $jr) {
                    if ($jr['id_joueur'] == $id) {
                        $is_selected = true;
                        break;
                    }
                }
                if ($is_selected) {
                    $selections_consecutives++;
                    if ($selections_consecutives > $max_selections_consecutives) {
                        $max_selections_consecutives = $selections_consecutives;
                    }
                } else {
                    $selections_consecutives = 0;
                }
            }
            $player_stats[$id]['selections_consecutives'] = $max_selections_consecutives;
        }
        
        return $player_stats;
    }
}
?>