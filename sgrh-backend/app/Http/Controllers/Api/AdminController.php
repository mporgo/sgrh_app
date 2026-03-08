<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\LogSysteme;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // ════════════════════════════════════════════════════════════════════
    // GESTION UTILISATEURS
    // ════════════════════════════════════════════════════════════════════

    // ── Liste des utilisateurs ────────────────────────────────────────
    public function users(Request $request): JsonResponse
    {
        $query = User::with('roles')->withCount('roles');

        if ($request->filled('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
            );
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('name')
                       ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'total'        => $users->total(),
                'per_page'     => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    // ── Détail utilisateur ────────────────────────────────────────────
    public function showUser(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    // ── Créer un utilisateur ──────────────────────────────────────────
    public function createUser(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'exists:roles,name'],
            'phone'    => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        LogSysteme::log(
            'user.create', 'administration',
            "Création de l'utilisateur {$user->name} ({$user->email})",
            null, ['id' => $user->id, 'email' => $user->email, 'role' => $request->role]
        );

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'data'    => new UserResource($user->load('roles')),
        ], 201);
    }

    // ── Modifier un utilisateur ───────────────────────────────────────
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name'     => ['sometimes', 'string', 'max:100'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['nullable', 'exists:roles,name'],
            'phone'    => ['nullable', 'string'],
            'is_active'=> ['sometimes', 'boolean'],
        ]);

        $avant = ['name' => $user->name, 'email' => $user->email, 'is_active' => $user->is_active];

        $data = $request->only(['name', 'email', 'phone', 'is_active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        LogSysteme::log(
            'user.update', 'administration',
            "Modification de l'utilisateur {$user->name}",
            $avant, ['name' => $user->name, 'email' => $user->email, 'role' => $request->role]
        );

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'data'    => new UserResource($user->load('roles')),
        ]);
    }

    // ── Activer / Désactiver un utilisateur ───────────────────────────
    public function toggleUser(User $user): JsonResponse
    {
        // Empêcher de se désactiver soi-même
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Impossible de modifier votre propre compte.'], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'activé' : 'désactivé';

        LogSysteme::log(
            'user.toggle', 'administration',
            "Compte utilisateur {$action} : {$user->name}",
        );

        return response()->json([
            'message' => "Compte {$action}.",
            'data'    => new UserResource($user->load('roles')),
        ]);
    }

    // ── Réinitialiser le mot de passe ─────────────────────────────────
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        LogSysteme::log(
            'user.reset_password', 'administration',
            "Réinitialisation du mot de passe de {$user->name}",
        );

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }

    // ════════════════════════════════════════════════════════════════════
    // GESTION DES RÔLES
    // ════════════════════════════════════════════════════════════════════

    // ── Liste des rôles ───────────────────────────────────────────────
    public function roles(): JsonResponse
    {
        $roles = Role::all()->map(fn($r) => [
            'id'       => $r->id,
            'name'     => $r->name,
            'nb_users' => $r->users()->count(),
        ]);

        return response()->json(['data' => $roles]);
    }

    // ── Assigner un rôle ──────────────────────────────────────────────
    public function assignerRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $ancienRole = $user->roles->pluck('name')->first();
        $user->syncRoles([$request->role]);

        LogSysteme::log(
            'user.role_change', 'administration',
            "Changement de rôle de {$user->name} : {$ancienRole} → {$request->role}",
        );

        return response()->json([
            'message' => 'Rôle assigné.',
            'data'    => new UserResource($user->load('roles')),
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // LOGS SYSTÈME
    // ════════════════════════════════════════════════════════════════════

    public function logs(Request $request): JsonResponse
    {
        $query = LogSysteme::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $logs->map(fn($l) => [
                'id'          => $l->id,
                'action'      => $l->action,
                'module'      => $l->module,
                'description' => $l->description,
                'ip_address'  => $l->ip_address,
                'user'        => $l->user ? [
                    'id'   => $l->user->id,
                    'name' => $l->user->name,
                ] : null,
                'donnees_avant'=> $l->donnees_avant,
                'donnees_apres'=> $l->donnees_apres,
                'created_at'   => $l->created_at?->format('Y-m-d H:i:s'),
                'date_relative'=> $l->created_at?->diffForHumans(),
            ]),
            'meta' => [
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // PARAMÈTRES SYSTÈME
    // ════════════════════════════════════════════════════════════════════

    public function infosSysteme(): JsonResponse
    {
        return response()->json([
            'data' => [
                'app_name'      => config('app.name'),
                'app_env'       => config('app.env'),
                'php_version'   => PHP_VERSION,
                'laravel'       => app()->version(),
                'db_name'       => config('database.connections.mysql.database'),
                'cache_driver'  => config('cache.default'),
                'nb_users'      => User::count(),
                'nb_actifs'     => User::where('is_active', true)->count(),
                'server_time'   => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
