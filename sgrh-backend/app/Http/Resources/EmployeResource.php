<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'matricule'       => $this->matricule,
            'statut'          => $this->statut,
            'date_embauche'   => $this->date_embauche?->format('Y-m-d'),
            'type_contrat'    => $this->type_contrat,
            'fin_contrat'     => $this->fin_contrat?->format('Y-m-d'),
            'salaire_base'    => $this->salaire_base,
            'conge_solde'     => $this->conge_solde,
            'notes'           => $this->notes,
            'contrat_expirant'=> $this->contrat_expirant,
            // Relations
            'user'            => new UserResource($this->whenLoaded('user')),
            'departement'     => new DepartementResource($this->whenLoaded('departement')),
            'poste'           => new PosteResource($this->whenLoaded('poste')),
            'manager'         => $this->whenLoaded('manager', fn() => [
                'id'   => $this->manager->id,
                'name' => $this->manager->name,
            ]),
        ];
    }
}
