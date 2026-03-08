<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titre'          => ['sometimes', 'string', 'max:200'],
            'description'    => ['nullable', 'string'],
            'formateur'      => ['nullable', 'string'],
            'type'           => ['sometimes', 'in:interne,externe,elearning'],
            'statut'         => ['sometimes', 'in:planifiee,en_cours,terminee,annulee'],
            'date_debut'     => ['sometimes', 'date'],
            'date_fin'       => ['sometimes', 'date'],
            'duree_heures'   => ['sometimes', 'integer', 'min:1'],
            'places_max'     => ['nullable', 'integer', 'min:1'],
            'cout'           => ['nullable', 'numeric', 'min:0'],
            'lieu'           => ['nullable', 'string'],
            'lien_elearning' => ['nullable', 'url'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
