<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElementVariableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'libelle'     => $this->libelle,
            'type'        => $this->type,
            'montant'     => $this->montant,
            'commentaire' => $this->commentaire,
        ];
    }
}
