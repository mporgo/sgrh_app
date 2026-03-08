<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormationRequest;
use App\Http\Requests\UpdateFormationRequest;
use App\Http\Resources\FormationResource;
use App\Models\Formation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Formation::withCount('inscriptions')
            ->with(['responsable']);

        // Charge l'inscription de l'utilisateur connecté si employé
        $employe = $request->user()->employe;
        if ($employe) {
            $query->with(['inscriptions' => fn($q) =>
                $q->where('employe_id', $employe->id)
            ]);
        }

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('titre', 'like', '%'.$request->search.'%');
        }

        $formations = $query->orderBy('date_debut', 'desc')
                            ->paginate($request->get('per_page', 12));

        return response()->json([
            'data' => FormationResource::collection($formations),
            'meta' => [
                'total'        => $formations->total(),
                'per_page'     => $formations->perPage(),
                'current_page' => $formations->currentPage(),
                'last_page'    => $formations->lastPage(),
            ],
        ]);
    }

    // ── Créer ─────────────────────────────────────────────────────────────────
    public function store(StoreFormationRequest $request): JsonResponse
    {
        $formation = Formation::create([
            ...$request->validated(),
            'responsable_id' => $request->responsable_id ?? $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Formation créée avec succès.',
            'data'    => new FormationResource($formation),
        ], 201);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Request $request, Formation $formation): JsonResponse
    {
        $formation->loadCount('inscriptions');
        $formation->load('responsable');

        $employe = $request->user()->employe;
        if ($employe) {
            $formation->load(['inscriptions' => fn($q) =>
                $q->where('employe_id', $employe->id)
            ]);
        }

        return response()->json([
            'data' => new FormationResource($formation),
        ]);
    }

    // ── Modifier ──────────────────────────────────────────────────────────────
    public function update(UpdateFormationRequest $request, Formation $formation): JsonResponse
    {
        if ($formation->statut === 'terminee') {
            return response()->json([
                'message' => 'Impossible de modifier une formation terminée.',
            ], 422);
        }

        $formation->update($request->validated());

        return response()->json([
            'message' => 'Formation mise à jour.',
            'data'    => new FormationResource($formation),
        ]);
    }

    // ── Supprimer / annuler ───────────────────────────────────────────────────
    public function destroy(Formation $formation): JsonResponse
    {
        if ($formation->statut === 'terminee') {
            return response()->json([
                'message' => 'Impossible de supprimer une formation terminée.',
            ], 422);
        }

        $formation->update(['statut' => 'annulee']);

        return response()->json(['message' => 'Formation annulée.']);
    }

    // ── Liste des inscrits d'une formation ────────────────────────────────────
    public function inscrits(Formation $formation): JsonResponse
    {
        $inscrits = $formation->inscriptions()
            ->with(['employe.user', 'employe.departement', 'employe.poste'])
            ->get();

        return response()->json([
            'data' => \App\Http\Resources\InscriptionFormationResource::collection($inscrits),
        ]);
    }
}
