<?php

namespace App\Services;

use App\Models\AttributionAvantage;
use App\Models\Employe;

class PaieCalculatorService
{
    // Taux CNSS Burkina Faso (part salarié)
    const TAUX_CNSS = 0.055;  // 5.5%

    // Tranches IUTS Burkina Faso (simplifié)
    const TRANCHES_IUTS = [
        ['min' => 0,       'max' => 30000,  'taux' => 0,    'deduction' => 0],
        ['min' => 30001,   'max' => 50000,  'taux' => 0.10, 'deduction' => 3000],
        ['min' => 50001,   'max' => 80000,  'taux' => 0.15, 'deduction' => 5500],
        ['min' => 80001,   'max' => 120000, 'taux' => 0.20, 'deduction' => 9500],
        ['min' => 120001,  'max' => 200000, 'taux' => 0.25, 'deduction' => 15500],
        ['min' => 200001,  'max' => 500000, 'taux' => 0.30, 'deduction' => 25500],
        ['min' => 500001,  'max' => PHP_INT_MAX, 'taux' => 0.35, 'deduction' => 50500],
    ];

    /**
     * Calcule les cotisations et le net à payer
     */
    public function calculer(
        float $salaireBase,
        float $totalPrimes,
        float $totalDeductions,
        float $totalAvantages
    ): array {
        $brut = $salaireBase + $totalPrimes + $totalAvantages;

        // CNSS sur salaire brut
        $cnss = round($brut * self::TAUX_CNSS, 2);

        // Base imposable IUTS = brut - CNSS
        $baseImposable = $brut - $cnss;

        // Calcul IUTS par tranches
        $iuts = $this->calculerIUTS($baseImposable);

        // Net à payer
        $net = round($brut - $cnss - $iuts - $totalDeductions, 2);

        return [
            'salaire_base'    => $salaireBase,
            'total_primes'    => $totalPrimes,
            'total_deductions'=> $totalDeductions,
            'total_avantages' => $totalAvantages,
            'cotisation_cnss' => $cnss,
            'impot_iuts'      => $iuts,
            'net_a_payer'     => max(0, $net),
        ];
    }

    /**
     * Calcule l'IUTS selon les tranches du Burkina Faso
     */
    private function calculerIUTS(float $baseImposable): float
    {
        foreach (self::TRANCHES_IUTS as $tranche) {
            if ($baseImposable >= $tranche['min'] && $baseImposable <= $tranche['max']) {
                return round(
                    ($baseImposable * $tranche['taux']) - $tranche['deduction'],
                    2
                );
            }
        }
        return 0;
    }

    /**
     * Récupère les avantages actifs d'un employé
     */
    public function getAvantagesEmploye(Employe $employe): array
    {
        $attributions = AttributionAvantage::with('avantage')
            ->where('employe_id', $employe->id)
            ->where('is_active', true)
            ->where('date_debut', '<=', now())
            ->where(fn($q) =>
                $q->whereNull('date_fin')
                  ->orWhere('date_fin', '>=', now())
            )
            ->get();

        $total = 0;
        $detail = [];

        foreach ($attributions as $attr) {
            $valeur = $attr->valeur_override ?? $attr->avantage->valeur;
            $total += $valeur;
            $detail[] = [
                'libelle' => $attr->avantage->libelle,
                'montant' => $valeur,
            ];
        }

        return ['total' => $total, 'detail' => $detail];
    }

    /**
     * Génère une référence unique pour le bulletin
     */
    public function genererReference(Employe $employe, int $mois, int $annee): string
    {
        return sprintf(
            'BULL-%d-%02d-%s',
            $annee,
            $mois,
            strtoupper($employe->matricule)
        );
    }
}
