<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeRequest;
use App\Http\Requests\UpdateEmployeRequest;
use App\Http\Resources\EmployeResource;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeController extends Controller
{
    // ── Liste avec filtres + pagination ──────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Employe::with(['user', 'departement', 'poste', 'manager'])
            ->withCount([]);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )->orWhere('matricule', 'like', "%{$search}%");
        }

        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        if ($request->filled('poste_id')) {
            $query->where('poste_id', $request->poste_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_contrat')) {
            $query->where('type_contrat', $request->type_contrat);
        }

        $employes = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => EmployeResource::collection($employes),
            'meta'  => [
                'total'        => $employes->total(),
                'per_page'     => $employes->perPage(),
                'current_page' => $employes->currentPage(),
                'last_page'    => $employes->lastPage(),
            ],
        ]);
    }

    // ── Créer un employé ─────────────────────────────────────────────────────
    public function store(StoreEmployeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // 1. Créer le compte User
            $password = Str::random(10);
            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($password),
                'phone'          => $request->phone,
                'date_naissance' => $request->date_naissance,
                'genre'          => $request->genre,
                'adresse'        => $request->adresse,
                'is_active'      => true,
            ]);

            // 2. Assigner le rôle "employe" par défaut
            $user->assignRole('employe');

            // 3. Générer le matricule
            $matricule = 'EMP-' . strtoupper(Str::random(6));

            // 4. Créer la fiche employé
            $employe = Employe::create([
                'user_id'        => $user->id,
                'departement_id' => $request->departement_id,
                'poste_id'       => $request->poste_id,
                'manager_id'     => $request->manager_id,
                'matricule'      => $matricule,
                'date_embauche'  => $request->date_embauche,
                'type_contrat'   => $request->type_contrat,
                'fin_contrat'    => $request->fin_contrat,
                'salaire_base'   => $request->salaire_base,
                'conge_solde'    => $request->conge_solde ?? 25,
                'notes'          => $request->notes,
            ]);

            // 5. TODO: envoyer email de bienvenue avec $password
            // Mail::to($user->email)->send(new WelcomeMail($user, $password));

            DB::commit();

            return response()->json([
                'message' => 'Employé créé avec succès.',
                'data'    => new EmployeResource($employe->load(['user', 'departement', 'poste'])),
                'password_temp' => $password, // à retirer en prod
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── Détail d'un employé ──────────────────────────────────────────────────
    public function show(Employe $employe): JsonResponse
    {
        $employe->load(['user', 'departement', 'poste', 'manager']);

        return response()->json([
            'data' => new EmployeResource($employe),
        ]);
    }

    // ── Modifier un employé ──────────────────────────────────────────────────
    public function update(UpdateEmployeRequest $request, Employe $employe): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Mise à jour User
            $employe->user->update($request->only([
                'name', 'email', 'phone',
                'date_naissance', 'genre', 'adresse',
            ]));

            // Mise à jour fiche Employe
            $employe->update($request->only([
                'departement_id', 'poste_id', 'manager_id',
                'date_embauche', 'type_contrat', 'fin_contrat',
                'salaire_base', 'conge_solde', 'statut', 'notes',
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Employé mis à jour avec succès.',
                'data'    => new EmployeResource($employe->load(['user', 'departement', 'poste'])),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur de mise à jour.', 'error' => $e->getMessage()], 500);
        }
    }

    // ── Désactiver (soft delete) ──────────────────────────────────────────────
    public function destroy(Employe $employe): JsonResponse
    {
        $employe->update(['statut' => 'inactif']);
        $employe->user->update(['is_active' => false]);
        $employe->delete(); // soft delete

        return response()->json(['message' => 'Employé désactivé avec succès.']);
    }
}
