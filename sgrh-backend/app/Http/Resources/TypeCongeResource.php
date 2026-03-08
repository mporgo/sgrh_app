<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeCongeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'libelle'             => $this->libelle,
            'jours_annuels'       => $this->jours_annuels,
            'reportable'          => $this->reportable,
            'justificatif_requis' => $this->justificatif_requis,
            'couleur'             => $this->couleur,
            'is_active'           => $this->is_active,
        ];
    }
}
