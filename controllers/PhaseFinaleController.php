<?php

require_once __DIR__ . '/../models/ModelPhaseFinale.php';

class PhaseFinaleController
{
    /**
     * Synchronise l'avancement des phases finales d'un tournoi donné.
     * Retourne un tableau ['succes' => bool, 'message' => ?string].
     */
    public function synchroniser($id_tournoi)
    {
        return ModelPhaseFinale::synchroniserTournoi((int)$id_tournoi);
    }
}
