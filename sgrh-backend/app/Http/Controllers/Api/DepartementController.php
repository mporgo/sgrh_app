<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartementResource;
use App\Models\Departement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index(): JsonResponse
    {
        $departements = Departement::withCount('employes')
            ->with('responsable')
            ->where('is_active', true)
            ->orderBy('nom')
            ->get();

        return response()->json(['data' => DepartementResource::collection($departements)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'            => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ]);

        $dept = Departement::create($data);

        return response()->json([
            'message' => 'Département créé.',
            'data'    => new DepartementResource($dept),
        ], 201);
    }

    public function update(Request $request, Departement $departement): JsonResponse
    {
        $data = $request->validate([
            'nom'            => ['sometimes', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'is_active'      => ['sometimes', 'boolean'],
        ]);

        $departement->update($data);

        return response()->json(['message' => 'Département mis à jour.', 'data' => new DepartementResource($departement)]);
    }

    public function destroy(Departement $departement): JsonResponse
    {
        $departement->update(['is_active' => false]);
        return response()->json(['message' => 'Département désactivé.']);
    }
}
