<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conge;
use App\Models\Employe;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Paie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    // ── Rapport global RH ─────────────────────────────────────────────────────
    public function global(Request $request): JsonResponse
    {
        $annee = $request->get('annee', now()->year);

        return response()->json([
            'data' => [
                'employes' => [
                    'total'        => Employe::count(),
                    'actifs'       => Employe::where('statut', 'actif')->count(),
                    'inactifs'     => Employe::where('statut', 'inactif')->count(),
                    'nouveaux'     => Employe::whereYear('created_at', $annee)->count(),
                    'par_contrat'  => Employe::selectRaw('type_contrat, count(*) as total')
                                        ->groupBy('type_contrat')
                                        ->pluck('total', 'type_contrat'),
                    'par_departement' => Employe::with('departement')
                                        ->selectRaw('departement_id, count(*) as total')
                                        ->groupBy('departement_id')
                                        ->get()
                                        ->map(fn($e) => [
                                            'departement' => $e->departement?->nom ?? 'Non défini',
                                            'total'       => $e->total,
                                        ]),
                ],
                'conges' => [
                    'total_annee'   => Conge::whereYear('date_debut', $annee)->count(),
                    'valides'       => Conge::whereYear('date_debut', $annee)->where('statut', 'valide')->count(),
                    'en_attente'    => Conge::where('statut', 'en_attente')->count(),
                    'jours_total'   => Conge::whereYear('date_debut', $annee)->where('statut', 'valide')->sum('nombre_jours'),
                    'par_mois'      => Conge::whereYear('date_debut', $annee)
                                        ->where('statut', 'valide')
                                        ->selectRaw('MONTH(date_debut) as mois, SUM(nombre_jours) as jours')
                                        ->groupBy('mois')
                                        ->orderBy('mois')
                                        ->pluck('jours', 'mois'),
                ],
                'evaluations' => [
                    'total'       => Evaluation::whereYear('date_evaluation', $annee)->count(),
                    'terminees'   => Evaluation::whereYear('date_evaluation', $annee)->where('statut', 'terminee')->count(),
                    'par_note'    => Evaluation::whereYear('date_evaluation', $annee)
                                        ->where('statut', 'terminee')
                                        ->selectRaw('note_globale, count(*) as total')
                                        ->groupBy('note_globale')
                                        ->pluck('total', 'note_globale'),
                    'score_moyen' => round(
                        Evaluation::whereYear('date_evaluation', $annee)
                            ->where('statut', 'terminee')
                            ->whereNotNull('score')
                            ->avg('score') ?? 0,
                        1
                    ),
                ],
                'formations' => [
                    'total'           => Formation::whereYear('date_debut', $annee)->count(),
                    'terminees'       => Formation::whereYear('date_debut', $annee)->where('statut', 'terminee')->count(),
                    'total_inscrits'  => InscriptionFormation::whereHas('formation', fn($q) =>
                                            $q->whereYear('date_debut', $annee)
                                        )->count(),
                    'certificats'     => InscriptionFormation::where('certificat_obtenu', true)
                                            ->whereHas('formation', fn($q) =>
                                                $q->whereYear('date_debut', $annee)
                                            )->count(),
                    'cout_total'      => Formation::whereYear('date_debut', $annee)->sum('cout'),
                ],
                'paie' => [
                    'masse_salariale_annee' => Paie::where('annee', $annee)->where('statut', 'paye')->sum('net_a_payer'),
                    'bulletins_annee'       => Paie::where('annee', $annee)->count(),
                    'par_mois'              => Paie::where('annee', $annee)
                                                ->where('statut', 'paye')
                                                ->selectRaw('mois, SUM(net_a_payer) as masse')
                                                ->groupBy('mois')
                                                ->orderBy('mois')
                                                ->pluck('masse', 'mois'),
                ],
            ],
        ]);
    }

    // ── Rapport absentéisme ───────────────────────────────────────────────────
    public function absenteisme(Request $request): JsonResponse
    {
        $annee = $request->get('annee', now()->year);
        $mois  = $request->get('mois');

        $query = Conge::with(['employe.user', 'employe.departement', 'typeConge'])
            ->where('statut', 'valide')
            ->whereYear('date_debut', $annee);

        if ($mois) {
            $query->whereMonth('date_debut', $mois);
        }

        $conges = $query->get();

        // Taux d'absentéisme par département
        $parDept = $conges->groupBy('employe.departement.nom')
            ->map(fn($items, $dept) => [
                'departement'  => $dept ?? 'Non défini',
                'nb_absences'  => $items->count(),
                'jours_total'  => $items->sum('nombre_jours'),
            ])->values();

        // Top 10 employés les plus absents
        $topAbsents = $conges->groupBy('employe.user.name')
            ->map(fn($items, $name) => [
                'employe'     => $name,
                'departement' => $items->first()->employe?->departement?->nom ?? '—',
                'nb_absences' => $items->count(),
                'jours_total' => $items->sum('nombre_jours'),
            ])
            ->sortByDesc('jours_total')
            ->take(10)
            ->values();

        return response()->json([
            'data' => [
                'total_jours'   => $conges->sum('nombre_jours'),
                'nb_employes'   => $conges->pluck('employe_id')->unique()->count(),
                'par_departement' => $parDept,
                'top_absents'   => $topAbsents,
                'par_mois'      => Conge::whereYear('date_debut', $annee)
                                    ->where('statut', 'valide')
                                    ->selectRaw('MONTH(date_debut) as mois, SUM(nombre_jours) as jours')
                                    ->groupBy('mois')
                                    ->orderBy('mois')
                                    ->pluck('jours', 'mois'),
            ],
        ]);
    }

    // ── Rapport masse salariale ───────────────────────────────────────────────
    public function masseSalariale(Request $request): JsonResponse
    {
        $annee = $request->get('annee', now()->year);

        $parMois = Paie::where('annee', $annee)
            ->where('statut', 'paye')
            ->selectRaw('mois,
                SUM(salaire_base) as base,
                SUM(total_primes) as primes,
                SUM(total_avantages) as avantages,
                SUM(cotisation_cnss) as cnss,
                SUM(impot_iuts) as iuts,
                SUM(net_a_payer) as net
            ')
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Masse par département
        $parDept = Paie::where('annee', $annee)
            ->where('statut', 'paye')
            ->with('employe.departement')
            ->get()
            ->groupBy('employe.departement.nom')
            ->map(fn($items, $dept) => [
                'departement' => $dept ?? 'Non défini',
                'masse_nette' => $items->sum('net_a_payer'),
                'nb_bulletins'=> $items->count(),
            ])
            ->values();

        return response()->json([
            'data' => [
                'total_net_annee'  => $parMois->sum('net'),
                'total_cnss_annee' => $parMois->sum('cnss'),
                'total_iuts_annee' => $parMois->sum('iuts'),
                'par_mois'         => $parMois,
                'par_departement'  => $parDept,
            ],
        ]);
    }

    // ── Rapport effectifs ─────────────────────────────────────────────────────
    public function effectifs(Request $request): JsonResponse
    {
        $employes = Employe::with(['user', 'departement', 'poste'])
            ->where('statut', 'actif')
            ->get();

        // Pyramide des âges
        $pyramide = $employes->filter(fn($e) => $e->user?->date_naissance)
            ->groupBy(function ($e) {
                $age = $e->user->date_naissance->age;
                if ($age < 25)      return 'Moins de 25 ans';
                if ($age < 35)      return '25-34 ans';
                if ($age < 45)      return '35-44 ans';
                if ($age < 55)      return '45-54 ans';
                return '55 ans et +';
            })
            ->map(fn($items, $tranche) => [
                'tranche' => $tranche,
                'total'   => $items->count(),
            ])->values();

        // Ancienneté moyenne
        $anciennetesMoyenne = round(
            $employes->filter(fn($e) => $e->date_embauche)
                ->avg(fn($e) => $e->date_embauche->diffInYears(now())) ?? 0,
            1
        );

        // Répartition genre
        $genres = $employes->groupBy(fn($e) => $e->user?->genre ?? 'non_precise')
            ->map(fn($items) => $items->count());

        // Contrats expirant dans 30 jours
        $contratsExpirants = Employe::with(['user', 'departement'])
            ->whereNotNull('fin_contrat')
            ->whereDate('fin_contrat', '>=', now())
            ->whereDate('fin_contrat', '<=', now()->addDays(30))
            ->get()
            ->map(fn($e) => [
                'nom'          => $e->user?->name,
                'departement'  => $e->departement?->nom,
                'type_contrat' => $e->type_contrat,
                'fin_contrat'  => $e->fin_contrat?->format('Y-m-d'),
                'jours_restants' => now()->diffInDays($e->fin_contrat),
            ]);

        return response()->json([
            'data' => [
                'total_actifs'          => $employes->count(),
                'anciennete_moyenne'    => $anciennetesMoyenne,
                'pyramide_ages'         => $pyramide,
                'repartition_genre'     => $genres,
                'contrats_expirants'    => $contratsExpirants,
                'par_departement'       => $employes->groupBy('departement.nom')
                    ->map(fn($items, $dept) => [
                        'departement' => $dept ?? 'Non défini',
                        'total'       => $items->count(),
                        'hommes'      => $items->filter(fn($e) => $e->user?->genre === 'M')->count(),
                        'femmes'      => $items->filter(fn($e) => $e->user?->genre === 'F')->count(),
                    ])->values(),
            ],
        ]);
    }
}
