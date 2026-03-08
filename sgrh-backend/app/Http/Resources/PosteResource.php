<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'titre'          => $this->titre,
            'description'    => $this->description,
            'salaire_min'    => $this->salaire_min,
            'salaire_max'    => $this->salaire_max,
            'is_active'      => $this->is_active,
            'departement'    => new DepartementResource($this->whenLoaded('departement')),
        ];
    }
}
