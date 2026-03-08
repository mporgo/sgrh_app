<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\Employe;
use App\Models\TypeConge;

class SoldeCongeService
{
    /**
     * Calcule le nombre de jours ouvrables entre deux dates
     * (exclut samedis et dimanches)
     */
    public function calculerJoursOuvrables(\DateTime $debut, \DateTime $fin): int
    {
        $jours = 0;
        $current = clone $debut;

        while ($current <= $fin) {
            $dayOfWeek = (int) $current->format('N');
            if ($dayOfWeek < 6) { // 1=Lun ... 5=Ven
                $jours++;
            }
            $current->modify('+1 day');
        }

        return $jours;
    }

    /**
     * Calcule le solde restant d'un employé pour un type de congé
     */
    public function getSolde(Employe $employe, TypeConge $typeConge): array
    {
        $annee = now()->year;

        // Total acquis (dotation annuelle du type)
        $total = $typeConge->jours_annuels;

        // Jours déjà pris (congés validés sur l'année courante)
        $pris = Conge::where('employe_id', $employe->id)
            ->where('type_conge_id', $typeConge->id)
            ->whereIn('statut', ['valide'])
            ->whereYear('date_debut', $annee)
            ->sum('nombre_jours');

        // Jours en attente
        $en_attente = Conge::where('employe_id', $employe->id)
            ->where('type_conge_id', $typeConge->id)
            ->where('statut', 'en_attente')
            ->whereYear('date_debut', $annee)
            ->sum('nombre_jours');

        return [
            'total'      => $total,
            'pris'       => $pris,
            'en_attente' => $en_attente,
            'restant'    => max(0, $total - $pris - $en_attente),
        ];
    }

    /**
     * Vérifie si le solde est suffisant
     */
    public function soldesSuffisant(Employe $employe, TypeConge $typeConge, int $jours): bool
    {
        $solde = $this->getSolde($employe, $typeConge);
        return $solde['restant'] >= $jours;
    }

    /**
     * Retourne tous les soldes d'un employé (tous types actifs)
     */
    public function tousLesSoldes(Employe $employe): array
    {
        $types = TypeConge::where('is_active', true)->get();

        return $types->map(function ($type) use ($employe) {
            $solde = $this->getSolde($employe, $type);
            return [
                'type_conge_id' => $type->id,
                'libelle'       => $type->libelle,
                'couleur'       => $type->couleur,
                ...$solde,
            ];
        })->toArray();
    }
}
