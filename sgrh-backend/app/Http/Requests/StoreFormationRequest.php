<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titre'          => ['required', 'string', 'max:200'],
            'description'    => ['nullable', 'string'],
            'formateur'      => ['nullable', 'string', 'max:150'],
            'type'           => ['required', 'in:interne,externe,elearning'],
            'date_debut'     => ['required', 'date'],
            'date_fin'       => ['required', 'date', 'after_or_equal:date_debut'],
            'duree_heures'   => ['required', 'integer', 'min:1'],
            'places_max'     => ['nullable', 'integer', 'min:1'],
            'cout'           => ['nullable', 'numeric', 'min:0'],
            'lieu'           => ['nullable', 'string'],
            'lien_elearning' => ['nullable', 'url'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'        => 'Le titre est obligatoire.',
            'type.required'         => 'Le type est obligatoire.',
            'date_debut.required'   => 'La date de début est obligatoire.',
            'date_fin.required'     => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être après la date de début.',
            'duree_heures.required' => 'La durée est obligatoire.',
        ];
    }
}
