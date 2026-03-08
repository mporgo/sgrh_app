<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'nb_employes' => $this->whenCounted('employes'),
            'responsable' => $this->whenLoaded('responsable', fn() => [
                'id'   => $this->responsable->id,
                'name' => $this->responsable->name,
            ]),
        ];
    }
}
