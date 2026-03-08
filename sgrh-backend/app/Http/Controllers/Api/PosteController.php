<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PosteResource;
use App\Models\Poste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Poste::with('departement')->where('is_active', true);

        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        return response()->json(['data' => PosteResource::collection($query->get())]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre'          => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'departement_id' => ['nullable', 'exists:departements,id'],
            'salaire_min'    => ['nullable', 'numeric', 'min:0'],
            'salaire_max'    => ['nullable', 'numeric', 'gte:salaire_min'],
        ]);

        $poste = Poste::create($data);

        return response()->json(['message' => 'Poste créé.', 'data' => new PosteResource($poste)], 201);
    }

    public function update(Request $request, Poste $poste): JsonResponse
    {
        $data = $request->validate([
            'titre'          => ['sometimes', 'string'],
            'description'    => ['nullable', 'string'],
            'departement_id' => ['nullable', 'exists:departements,id'],
            'salaire_min'    => ['nullable', 'numeric'],
            'salaire_max'    => ['nullable', 'numeric'],
            'is_active'      => ['sometimes', 'boolean'],
        ]);

        $poste->update($data);
        return response()->json(['message' => 'Poste mis à jour.', 'data' => new PosteResource($poste)]);
    }
}
