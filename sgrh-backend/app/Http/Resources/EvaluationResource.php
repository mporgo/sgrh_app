<?php

namespace App\Http\Resources;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'date_evaluation'        => $this->date_evaluation?->format('Y-m-d'),
            'date_prochaine'         => $this->date_prochaine?->format('Y-m-d'),
            'type'                   => $this->type,
            'type_label'             => Evaluation::$types[$this->type] ?? $this->type,
            'statut'                 => $this->statut,
            'note_globale'           => $this->note_globale,
            'note_label'             => $this->note_globale
                                        ? Evaluation::$notes[$this->note_globale]
                                        : null,
            'score'                  => $this->score,
            'objectifs_fixes'        => $this->objectifs_fixes,
            'objectifs_atteints'     => $this->objectifs_atteints,
            'points_forts'           => $this->points_forts,
            'axes_amelioration'      => $this->axes_amelioration,
            'commentaire_evaluateur' => $this->commentaire_evaluateur,
            'commentaire_employe'    => $this->commentaire_employe,
            'signe_employe'          => $this->signe_employe,
            'signe_evaluateur'       => $this->signe_evaluateur,
            'employe'                => $this->whenLoaded('employe', fn() => [
                'id'          => $this->employe->id,
                'matricule'   => $this->employe->matricule,
                'departement' => $this->employe->departement?->nom,
                'poste'       => $this->employe->poste?->titre,
                'user'        => [
                    'id'   => $this->employe->user->id,
                    'name' => $this->employe->user->name,
                ],
            ]),
            'evaluateur'             => $this->whenLoaded('evaluateur', fn() => [
                'id'   => $this->evaluateur->id,
                'name' => $this->evaluateur->name,
            ]),
        ];
    }
}
