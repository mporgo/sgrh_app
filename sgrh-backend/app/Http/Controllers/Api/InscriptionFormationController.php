<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValiderInscriptionRequest;
use App\Http\Resources\InscriptionFormationResource;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionFormationController extends Controller
{
    // ── S'inscrire à une formation ────────────────────────────────────────────
    public function inscrire(Request $request, Formation $formation): JsonResponse
    {
        $employe = $request->user()->employe;

        if (!$employe) {
            return response()->json(['message' => 'Fiche employé introuvable.'], 404);
        }

        // Vérifier si déjà inscrit
        $dejaInscrit = InscriptionFormation::where('formation_id', $formation->id)
            ->where('employe_id', $employe->id)
            ->whereNotIn('statut', ['refusee', 'abandonnee'])
            ->exists();

        if ($dejaInscrit) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cette formation.'], 422);
        }

        // Vérifier les places disponibles
        if ($formation->complet) {
            return response()->json(['message' => 'Cette formation est complète.'], 422);
        }

        // Vérifier la formation n'est pas terminée/annulée
        if (in_array($formation->statut, ['terminee', 'annulee'])) {
            return response()->json(['message' => 'Impossible de s\'inscrire à cette formation.'], 422);
        }

        $inscription = InscriptionFormation::create([
            'formation_id' => $formation->id,
            'employe_id'   => $employe->id,
            'statut'       => 'en_attente',
            'inscrit_le'   => now(),
        ]);

        // TODO: notifier le responsable de la formation

        return response()->json([
            'message' => 'Inscription soumise avec succès.',
            'data'    => new InscriptionFormationResource($inscription->load(['formation', 'employe.user'])),
        ], 201);
    }

    // ── Se désinscrire ────────────────────────────────────────────────────────
    public function desinscrire(Request $request, Formation $formation): JsonResponse
    {
        $employe = $request->user()->employe;

        $inscription = InscriptionFormation::where('formation_id', $formation->id)
            ->where('employe_id', $employe->id)
            ->whereIn('statut', ['en_attente', 'validee'])
            ->first();

        if (!$inscription) {
            return response()->json(['message' => 'Inscription introuvable.'], 404);
        }

        $inscription->update(['statut' => 'abandonnee']);

        return response()->json(['message' => 'Désinscription effectuée.']);
    }

    // ── Valider ou refuser une inscription (RH/Admin) ─────────────────────────
    public function valider(ValiderInscriptionRequest $request, InscriptionFormation $inscription): JsonResponse
    {
        if ($inscription->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette inscription a déjà été traitée.'], 422);
        }

        $inscription->update([
            'statut'      => $request->action === 'valider' ? 'validee' : 'refusee',
            'commentaire' => $request->commentaire,
        ]);

        // TODO: notifier l'employé

        return response()->json([
            'message' => $request->action === 'valider' ? 'Inscription validée.' : 'Inscription refusée.',
            'data'    => new InscriptionFormationResource($inscription->load(['employe.user', 'formation'])),
        ]);
    }

    // ── Renseigner les résultats d'un inscrit ─────────────────────────────────
    public function resultats(Request $request, InscriptionFormation $inscription): JsonResponse
    {
        $request->validate([
            'note'             => ['nullable', 'integer', 'min:0', 'max:20'],
            'certificat_obtenu'=> ['sometimes', 'boolean'],
            'commentaire'      => ['nullable', 'string'],
        ]);

        $inscription->update([
            ...$request->only(['note', 'certificat_obtenu', 'commentaire']),
            'statut' => 'terminee',
        ]);

        return response()->json([
            'message' => 'Résultats enregistrés.',
            'data'    => new InscriptionFormationResource($inscription->load(['employe.user', 'formation'])),
        ]);
    }

    // ── Mes formations (employé connecté) ─────────────────────────────────────
    public function mesFormations(Request $request): JsonResponse
    {
        $employe = $request->user()->employe;

        if (!$employe) {
            return response()->json(['message' => 'Fiche employé introuvable.'], 404);
        }

        $inscriptions = InscriptionFormation::with(['formation'])
            ->where('employe_id', $employe->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => InscriptionFormationResource::collection($inscriptions),
        ]);
    }
}
