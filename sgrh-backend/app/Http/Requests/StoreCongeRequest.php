<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCongeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type_conge_id' => ['required', 'exists:type_conges,id'],
            'date_debut'    => ['required', 'date', 'after_or_equal:today'],
            'date_fin'      => ['required', 'date', 'after_or_equal:date_debut'],
            'commentaire'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type_conge_id.required' => 'Le type de congé est obligatoire.',
            'date_debut.required'    => 'La date de début est obligatoire.',
            'date_debut.after_or_equal' => 'La date de début doit être aujourd\'hui ou dans le futur.',
            'date_fin.required'      => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal'=> 'La date de fin doit être après la date de début.',
        ];
    }
}
