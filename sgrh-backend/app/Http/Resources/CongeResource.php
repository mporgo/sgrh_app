<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CongeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'date_debut'   => $this->date_debut?->format('Y-m-d'),
            'date_fin'     => $this->date_fin?->format('Y-m-d'),
            'nombre_jours' => $this->nombre_jours,
            'commentaire'  => $this->commentaire,
            'motif_refus'  => $this->motif_refus,
            'statut'       => $this->statut,
            'valide_le'    => $this->valide_le?->format('Y-m-d H:i'),
            'type_conge'   => new TypeCongeResource($this->whenLoaded('typeConge')),
            'employe'      => $this->whenLoaded('employe', fn() => [
                'id'        => $this->employe->id,
                'matricule' => $this->employe->matricule,
                'user'      => [
                    'id'   => $this->employe->user->id,
                    'name' => $this->employe->user->name,
                ],
                'departement' => $this->employe->departement?->nom,
            ]),
            'valideur'     => $this->whenLoaded('valideur', fn() => [
                'id'   => $this->valideur->id,
                'name' => $this->valideur->name,
            ]),
        ];
    }
}
