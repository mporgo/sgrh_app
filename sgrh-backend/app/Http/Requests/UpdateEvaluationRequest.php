<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date_evaluation'        => ['sometimes', 'date'],
            'date_prochaine'         => ['nullable', 'date'],
            'type'                   => ['sometimes', 'in:annuelle,semestrielle,periode_essai,autre'],
            'statut'                 => ['sometimes', 'in:planifiee,en_cours,terminee,annulee'],
            'note_globale'           => ['nullable', 'in:insuffisant,passable,bien,tres_bien,excellent'],
            'score'                  => ['nullable', 'integer', 'min:0', 'max:100'],
            'objectifs_fixes'        => ['nullable', 'string'],
            'objectifs_atteints'     => ['nullable', 'string'],
            'points_forts'           => ['nullable', 'string'],
            'axes_amelioration'      => ['nullable', 'string'],
            'commentaire_evaluateur' => ['nullable', 'string'],
            'commentaire_employe'    => ['nullable', 'string'],
            'signe_employe'          => ['sometimes', 'boolean'],
            'signe_evaluateur'       => ['sometimes', 'boolean'],
        ];
    }
}
