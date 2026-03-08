<?php

namespace App\Http\Resources;

use App\Models\Paie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'mois'             => $this->mois,
            'mois_label'       => $this->mois_label,
            'annee'            => $this->annee,
            'periode'          => $this->mois_label . ' ' . $this->annee,
            'statut'           => $this->statut,
            'salaire_base'     => $this->salaire_base,
            'total_primes'     => $this->total_primes,
            'total_deductions' => $this->total_deductions,
            'total_avantages'  => $this->total_avantages,
            'cotisation_cnss'  => $this->cotisation_cnss,
            'impot_iuts'       => $this->impot_iuts,
            'net_a_payer'      => $this->net_a_payer,
            'date_paiement'    => $this->date_paiement?->format('Y-m-d'),
            'notes'            => $this->notes,
            'elements'         => ElementVariableResource::collection($this->whenLoaded('elements')),
            'employe'          => $this->whenLoaded('employe', fn() => [
                'id'          => $this->employe->id,
                'matricule'   => $this->employe->matricule,
                'departement' => $this->employe->departement?->nom,
                'poste'       => $this->employe->poste?->titre,
                'user'        => [
                    'id'   => $this->employe->user->id,
                    'name' => $this->employe->user->name,
                ],
            ]),
            'genere_par'       => $this->whenLoaded('generepar', fn() => [
                'id'   => $this->generepar->id,
                'name' => $this->generepar->name,
            ]),
        ];
    }
}
