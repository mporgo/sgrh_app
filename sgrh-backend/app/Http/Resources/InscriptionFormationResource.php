<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionFormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'statut'           => $this->statut,
            'note'             => $this->note,
            'certificat_obtenu'=> $this->certificat_obtenu,
            'commentaire'      => $this->commentaire,
            'inscrit_le'       => $this->inscrit_le?->format('Y-m-d H:i'),
            'formation'        => new FormationResource($this->whenLoaded('formation')),
            'employe'          => $this->whenLoaded('employe', fn() => [
                'id'        => $this->employe->id,
                'matricule' => $this->employe->matricule,
                'user'      => [
                    'id'   => $this->employe->user->id,
                    'name' => $this->employe->user->name,
                ],
                'departement' => $this->employe->departement?->nom,
                'poste'       => $this->employe->poste?->titre,
            ]),
        ];
    }
}
