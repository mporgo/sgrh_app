<?php

namespace App\Http\Resources;

use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'titre'               => $this->titre,
            'description'         => $this->description,
            'formateur'           => $this->formateur,
            'type'                => $this->type,
            'type_label'          => Formation::$types[$this->type] ?? $this->type,
            'statut'              => $this->statut,
            'date_debut'          => $this->date_debut?->format('Y-m-d'),
            'date_fin'            => $this->date_fin?->format('Y-m-d'),
            'duree_heures'        => $this->duree_heures,
            'places_max'          => $this->places_max,
            'places_disponibles'  => $this->places_disponibles,
            'complet'             => $this->complet,
            'cout'                => $this->cout,
            'lieu'                => $this->lieu,
            'lien_elearning'      => $this->lien_elearning,
            'nb_inscrits'         => $this->whenCounted('inscriptions'),
            'responsable'         => $this->whenLoaded('responsable', fn() => [
                'id'   => $this->responsable->id,
                'name' => $this->responsable->name,
            ]),
            'mon_inscription'     => $this->whenLoaded('inscriptions', function () use ($request) {
                $employe = $request->user()?->employe;
                if (!$employe) return null;
                return $this->inscriptions
                    ->firstWhere('employe_id', $employe->id);
            }),
        ];
    }
}
