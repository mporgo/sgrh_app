<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCongeRequest;
use App\Http\Requests\TraiterCongeRequest;
use App\Http\Resources\CongeResource;
use App\Http\Resources\TypeCongeResource;
use App\Models\Conge;
use App\Models\TypeConge;
use App\Services\SoldeCongeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    public function __construct(private SoldeCongeService $soldeService) {}

    // ── Liste des congés (filtrée selon le rôle) ─────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Conge::with(['employe.user', 'employe.departement', 'typeConge', 'valideur']);

        // Employé : voit uniquement ses propres congés
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

        if ($request->filled('type_conge_id')) {
            $query->where('type_conge_id', $request->type_conge_id);
        }

        if ($request->filled('mois') && $request->filled('annee')) {
            $query->whereMonth('date_debut', $request->mois)
                  ->whereYear('date_debut', $request->annee);
        }

        if ($request->filled('annee')) {
            $query->whereYear('date_debut', $request->annee);
        }

        $conges = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => CongeResource::collection($conges),
            'meta' => [
                'total'        => $conges->total(),
                'per_page'     => $conges->perPage(),
                'current_page' => $conges->currentPage(),
                'last_page'    => $conges->lastPage(),
            ],
        ]);
    }

    // ── Soumettre une demande ─────────────────────────────────────────────────
    public function store(StoreCongeRequest $request): JsonResponse
    {
        $user    = $request->user();
        $employe = $user->employe;

        if (!$employe) {
            return response()->json(['message' => 'Fiche employé introuvable.'], 404);
        }

        $typeConge = TypeConge::findOrFail($request->type_conge_id);

        // Calculer les jours ouvrables
        $debut = new \DateTime($request->date_debut);
        $fin   = new \DateTime($request->date_fin);
        $jours = $this->soldeService->calculerJoursOuvrables($debut, $fin);

        // Vérifier le solde
        if ($typeConge->jours_annuels > 0 && !$this->soldeService->soldesSuffisant($employe, $typeConge, $jours)) {
            return response()->json([
                'message' => 'Solde de congés insuffisant pour cette demande.',
                'solde'   => $this->soldeService->getSolde($employe, $typeConge),
            ], 422);
        }

        // Vérifier les chevauchements
        $chevauchement = Conge::where('employe_id', $employe->id)
            ->whereNotIn('statut', ['refuse', 'annule'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                  ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin]);
            })->exists();

        if ($chevauchement) {
            return response()->json([
                'message' => 'Une demande de congé chevauche déjà cette période.',
            ], 422);
        }

        $conge = Conge::create([
            'employe_id'    => $employe->id,
            'type_conge_id' => $request->type_conge_id,
            'date_debut'    => $request->date_debut,
            'date_fin'      => $request->date_fin,
            'nombre_jours'  => $jours,
            'commentaire'   => $request->commentaire,
            'statut'        => 'en_attente',
        ]);

        // TODO: notifier le manager

        return response()->json([
            'message' => 'Demande soumise avec succès.',
            'data'    => new CongeResource($conge->load(['typeConge', 'employe.user'])),
        ], 201);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Conge $conge): JsonResponse
    {
        return response()->json([
            'data' => new CongeResource($conge->load(['employe.user', 'employe.departement', 'typeConge', 'valideur'])),
        ]);
    }

    // ── Valider ou refuser ────────────────────────────────────────────────────
    public function traiter(TraiterCongeRequest $request, Conge $conge): JsonResponse
    {
        if ($conge->statut !== 'en_attente') {
            return response()->json([
                'message' => 'Cette demande a déjà été traitée.',
            ], 422);
        }

        $user = $request->user();

        if ($request->action === 'valider') {
            $conge->update([
                'statut'     => 'valide',
                'valideur_id'=> $user->id,
                'valide_le'  => now(),
            ]);
            // TODO: notifier l'employé
            $message = 'Demande validée avec succès.';
        } else {
            $conge->update([
                'statut'      => 'refuse',
                'valideur_id' => $user->id,
                'motif_refus' => $request->motif_refus,
                'valide_le'   => now(),
            ]);
            // TODO: notifier l'employé
            $message = 'Demande refusée.';
        }

        return response()->json([
            'message' => $message,
            'data'    => new CongeResource($conge->load(['typeConge', 'employe.user', 'valideur'])),
        ]);
    }

    // ── Annuler (par l'employé) ───────────────────────────────────────────────
    public function annuler(Request $request, Conge $conge): JsonResponse
    {
        $user = $request->user();

        // Seul l'employé concerné peut annuler sa propre demande en attente
        if ($conge->employe->user_id !== $user->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        if (!in_array($conge->statut, ['en_attente'])) {
            return response()->json(['message' => 'Impossible d\'annuler cette demande.'], 422);
        }

        $conge->update(['statut' => 'annule']);

        return response()->json(['message' => 'Demande annulée.']);
    }

    // ── Soldes de l'utilisateur connecté ─────────────────────────────────────
    public function mesSoldes(Request $request): JsonResponse
    {
        $employe = $request->user()->employe;

        if (!$employe) {
            return response()->json(['message' => 'Fiche employé introuvable.'], 404);
        }

        return response()->json([
            'data' => $this->soldeService->tousLesSoldes($employe),
        ]);
    }

    // ── Types de congés ───────────────────────────────────────────────────────
    public function typeConges(): JsonResponse
    {
        $types = TypeConge::where('is_active', true)->orderBy('libelle')->get();
        return response()->json(['data' => TypeCongeResource::collection($types)]);
    }

    // ── Calendrier (tous les congés validés d'une période) ───────────────────
    public function calendrier(Request $request): JsonResponse
    {
        $annee = $request->get('annee', now()->year);
        $mois  = $request->get('mois', now()->month);

        $conges = Conge::with(['employe.user', 'typeConge'])
            ->where('statut', 'valide')
            ->whereYear('date_debut', $annee)
            ->whereMonth('date_debut', $mois)
            ->get()
            ->map(fn($c) => [
                'id'     => $c->id,
                'title'  => $c->employe->user->name,
                'start'  => $c->date_debut->format('Y-m-d'),
                'end'    => $c->date_fin->addDay()->format('Y-m-d'), // FullCalendar end est exclusif
                'color'  => $c->typeConge->couleur,
                'type'   => $c->typeConge->libelle,
            ]);

        return response()->json(['data' => $conges]);
    }
}
