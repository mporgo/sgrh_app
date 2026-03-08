<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenererPaieRequest;
use App\Http\Requests\UpdatePaieRequest;
use App\Http\Resources\PaieResource;
use App\Models\Employe;
use App\Models\Paie;
use App\Services\PaieCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaieController extends Controller
{
    public function __construct(private PaieCalculatorService $calculator) {}

    // ── Liste des bulletins ───────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Paie::with(['employe.user', 'employe.departement', 'employe.poste', 'generepar']);

        // Employé : voit uniquement ses propres bulletins
        if ($user->hasRole('employe') && !$user->hasRole(['rh', 'admin'])) {
            $query->whereHas('employe', fn($q) => $q->where('user_id', $user->id));
        }

        // Filtres
        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('mois')) {
            $query->where('mois', $request->mois);
        }
        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $paies = $query->orderBy('annee', 'desc')
                       ->orderBy('mois', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => PaieResource::collection($paies),
            'meta' => [
                'total'        => $paies->total(),
                'per_page'     => $paies->perPage(),
                'current_page' => $paies->currentPage(),
                'last_page'    => $paies->lastPage(),
            ],
        ]);
    }

    // ── Générer un bulletin ───────────────────────────────────────────────────
    public function store(GenererPaieRequest $request): JsonResponse
    {
        $employe = Employe::with(['user', 'departement', 'poste'])->findOrFail($request->employe_id);

        // Vérifier qu'un bulletin n'existe pas déjà pour ce mois
        $exists = Paie::where('employe_id', $employe->id)
            ->where('mois', $request->mois)
            ->where('annee', $request->annee)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Un bulletin existe déjà pour cet employé sur cette période.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Calcul des éléments variables
            $elements         = $request->elements ?? [];
            $totalPrimes      = collect($elements)->where('type', 'prime')->sum('montant');
            $totalDeductions  = collect($elements)->where('type', 'deduction')->sum('montant');

            // Avantages actifs de l'employé
            $avantages        = $this->calculator->getAvantagesEmploye($employe);
            $totalAvantages   = $avantages['total'];

            // Calcul net à payer
            $calcul = $this->calculator->calculer(
                (float) $employe->salaire_base,
                (float) $totalPrimes,
                (float) $totalDeductions,
                (float) $totalAvantages,
            );

            // Créer le bulletin
            $paie = Paie::create([
                ...$calcul,
                'employe_id'   => $employe->id,
                'mois'         => $request->mois,
                'annee'        => $request->annee,
                'statut'       => 'brouillon',
                'reference'    => $this->calculator->genererReference($employe, $request->mois, $request->annee),
                'notes'        => $request->notes,
                'genere_par'   => $request->user()->id,
            ]);

            // Créer les éléments variables saisis
            foreach ($elements as $el) {
                $paie->elements()->create($el);
            }

            // Ajouter les avantages auto comme éléments
            foreach ($avantages['detail'] as $av) {
                $paie->elements()->create([
                    'libelle'     => $av['libelle'],
                    'type'        => 'avantage',
                    'montant'     => $av['montant'],
                    'commentaire' => 'Avantage attribué automatiquement',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulletin généré avec succès.',
                'data'    => new PaieResource($paie->load(['employe.user', 'elements'])),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la génération.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── Détail d'un bulletin ──────────────────────────────────────────────────
    public function show(Paie $paie): JsonResponse
    {
        return response()->json([
            'data' => new PaieResource(
                $paie->load(['employe.user', 'employe.departement', 'employe.poste', 'elements', 'generepar'])
            ),
        ]);
    }

    // ── Modifier un bulletin (brouillon seulement) ────────────────────────────
    public function update(UpdatePaieRequest $request, Paie $paie): JsonResponse
    {
        if ($paie->statut !== 'brouillon') {
            return response()->json([
                'message' => 'Seul un bulletin en brouillon peut être modifié.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $elements = $request->elements ?? [];

            // Recalcul
            $totalPrimes     = collect($elements)->where('type', 'prime')->sum('montant');
            $totalDeductions = collect($elements)->where('type', 'deduction')->sum('montant');
            $totalAvantages  = collect($elements)->where('type', 'avantage')->sum('montant');

            $calcul = $this->calculator->calculer(
                (float) $paie->salaire_base,
                (float) $totalPrimes,
                (float) $totalDeductions,
                (float) $totalAvantages,
            );

            $paie->update([
                ...$calcul,
                'notes'         => $request->notes ?? $paie->notes,
                'date_paiement' => $request->date_paiement ?? $paie->date_paiement,
            ]);

            // Remplacer les éléments variables
            $paie->elements()->delete();
            foreach ($elements as $el) {
                $paie->elements()->create($el);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulletin mis à jour.',
                'data'    => new PaieResource($paie->load(['employe.user', 'elements'])),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur.', 'error' => $e->getMessage()], 500);
        }
    }

    // ── Valider un bulletin ───────────────────────────────────────────────────
    public function valider(Paie $paie): JsonResponse
    {
        if ($paie->statut !== 'brouillon') {
            return response()->json(['message' => 'Ce bulletin est déjà validé ou payé.'], 422);
        }

        $paie->update(['statut' => 'valide']);

        return response()->json([
            'message' => 'Bulletin validé.',
            'data'    => new PaieResource($paie->load(['employe.user', 'elements'])),
        ]);
    }

    // ── Marquer comme payé ────────────────────────────────────────────────────
    public function marquerPaye(Request $request, Paie $paie): JsonResponse
    {
        $request->validate([
            'date_paiement' => ['required', 'date'],
        ]);

        if ($paie->statut !== 'valide') {
            return response()->json(['message' => 'Le bulletin doit être validé avant d\'être marqué payé.'], 422);
        }

        $paie->update([
            'statut'        => 'paye',
            'date_paiement' => $request->date_paiement,
        ]);

        return response()->json([
            'message' => 'Bulletin marqué comme payé.',
            'data'    => new PaieResource($paie->load(['employe.user', 'elements'])),
        ]);
    }

    // ── Supprimer (brouillon seulement) ───────────────────────────────────────
    public function destroy(Paie $paie): JsonResponse
    {
        if ($paie->statut !== 'brouillon') {
            return response()->json(['message' => 'Seul un bulletin en brouillon peut être supprimé.'], 422);
        }

        $paie->elements()->delete();
        $paie->delete();

        return response()->json(['message' => 'Bulletin supprimé.']);
    }

    // ── Aperçu de calcul avant génération ────────────────────────────────────
    public function apercu(Request $request): JsonResponse
    {
        $request->validate([
            'employe_id' => ['required', 'exists:employes,id'],
            'elements'   => ['nullable', 'array'],
        ]);

        $employe         = Employe::findOrFail($request->employe_id);
        $elements        = $request->elements ?? [];
        $totalPrimes     = collect($elements)->where('type', 'prime')->sum('montant');
        $totalDeductions = collect($elements)->where('type', 'deduction')->sum('montant');
        $avantages       = $this->calculator->getAvantagesEmploye($employe);

        $calcul = $this->calculator->calculer(
            (float) $employe->salaire_base,
            (float) $totalPrimes,
            (float) $totalDeductions,
            (float) $avantages['total'],
        );

        return response()->json([
            'data' => [
                ...$calcul,
                'avantages_detail' => $avantages['detail'],
            ],
        ]);
    }

    // ── Stats paie (RH/Admin) ─────────────────────────────────────────────────
    public function stats(Request $request): JsonResponse
    {
        $mois  = $request->get('mois',  now()->month);
        $annee = $request->get('annee', now()->year);

        return response()->json([
            'data' => [
                'total_bulletins' => Paie::where('mois', $mois)->where('annee', $annee)->count(),
                'total_net'       => Paie::where('mois', $mois)->where('annee', $annee)->sum('net_a_payer'),
                'brouillons'      => Paie::where('mois', $mois)->where('annee', $annee)->where('statut', 'brouillon')->count(),
                'valides'         => Paie::where('mois', $mois)->where('annee', $annee)->where('statut', 'valide')->count(),
                'payes'           => Paie::where('mois', $mois)->where('annee', $annee)->where('statut', 'paye')->count(),
                'masse_salariale' => Paie::where('mois', $mois)->where('annee', $annee)->where('statut', 'paye')->sum('net_a_payer'),
            ],
        ]);
    }
}
