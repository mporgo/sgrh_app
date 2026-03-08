<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Evaluation::with(['employe.user', 'employe.departement', 'employe.poste', 'evaluateur']);

        // Employé : voit seulement ses évaluations
        if ($user->hasRole('employe') && !$user->hasRole(['manager', 'rh', 'admin'])) {
            $query->whereHas('employe', fn($q) => $q->where('user_id', $user->id));
        }

        // Manager : voit ses collaborateurs + lui-même
        if ($user->hasRole('manager') && !$user->hasRole(['rh', 'admin'])) {
            $query->whereHas('employe', fn($q) =>
                $q->where('user_id', $user->id)
                  ->orWhere('manager_id', $user->id)
            );
        }

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_evaluation', $request->annee);
        }

        $evaluations = $query->orderBy('date_evaluation', 'desc')
                             ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => EvaluationResource::collection($evaluations),
            'meta' => [
                'total'        => $evaluations->total(),
                'per_page'     => $evaluations->perPage(),
                'current_page' => $evaluations->currentPage(),
                'last_page'    => $evaluations->lastPage(),
            ],
        ]);
    }

    // ── Planifier une évaluation ──────────────────────────────────────────────
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        $evaluation = Evaluation::create([
            ...$request->validated(),
            'evaluateur_id' => $request->user()->id,
            'statut'        => 'planifiee',
        ]);

        // TODO : notifier l'employé

        return response()->json([
            'message' => 'Évaluation planifiée avec succès.',
            'data'    => new EvaluationResource($evaluation->load(['employe.user', 'evaluateur'])),
        ], 201);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Evaluation $evaluation): JsonResponse
    {
        return response()->json([
            'data' => new EvaluationResource(
                $evaluation->load(['employe.user', 'employe.departement', 'employe.poste', 'evaluateur'])
            ),
        ]);
    }

    // ── Modifier / Renseigner les résultats ───────────────────────────────────
    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        // Empêcher la modification d'une évaluation terminée
        if ($evaluation->statut === 'terminee') {
            return response()->json([
                'message' => 'Impossible de modifier une évaluation terminée.',
            ], 422);
        }

        $evaluation->update($request->validated());

        // Si note renseignée → passer automatiquement à "terminée"
        if ($request->filled('note_globale') && $evaluation->statut !== 'terminee') {
            $evaluation->update(['statut' => 'terminee']);
        }

        return response()->json([
            'message' => 'Évaluation mise à jour.',
            'data'    => new EvaluationResource($evaluation->load(['employe.user', 'evaluateur'])),
        ]);
    }

    // ── Supprimer (annuler) ───────────────────────────────────────────────────
    public function destroy(Evaluation $evaluation): JsonResponse
    {
        if ($evaluation->statut === 'terminee') {
            return response()->json([
                'message' => 'Impossible de supprimer une évaluation terminée.',
            ], 422);
        }

        $evaluation->update(['statut' => 'annulee']);

        return response()->json(['message' => 'Évaluation annulée.']);
    }

    // ── Commentaire de l'employé ──────────────────────────────────────────────
    public function commenterEmploye(Request $request, Evaluation $evaluation): JsonResponse
    {
        $request->validate([
            'commentaire_employe' => ['required', 'string', 'max:1000'],
        ]);

        // Vérifier que c'est bien l'employé concerné
        if ($evaluation->employe->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        if ($evaluation->statut !== 'terminee') {
            return response()->json(['message' => 'L\'évaluation n\'est pas encore terminée.'], 422);
        }

        $evaluation->update([
            'commentaire_employe' => $request->commentaire_employe,
            'signe_employe'       => true,
        ]);

        return response()->json([
            'message' => 'Commentaire enregistré.',
            'data'    => new EvaluationResource($evaluation->load(['employe.user', 'evaluateur'])),
        ]);
    }

    // ── Statistiques (pour dashboard RH) ─────────────────────────────────────
    public function stats(Request $request): JsonResponse
    {
        $annee = $request->get('annee', now()->year);

        return response()->json([
            'data' => [
                'total'      => Evaluation::whereYear('date_evaluation', $annee)->count(),
                'planifiees' => Evaluation::whereYear('date_evaluation', $annee)->where('statut', 'planifiee')->count(),
                'terminees'  => Evaluation::whereYear('date_evaluation', $annee)->where('statut', 'terminee')->count(),
                'par_note'   => Evaluation::whereYear('date_evaluation', $annee)
                                    ->where('statut', 'terminee')
                                    ->selectRaw('note_globale, count(*) as total')
                                    ->groupBy('note_globale')
                                    ->pluck('total', 'note_globale'),
            ],
        ]);
    }
}
