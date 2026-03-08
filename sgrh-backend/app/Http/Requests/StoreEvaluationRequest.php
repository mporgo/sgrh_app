<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'      => ['required', 'exists:employes,id'],
            'date_evaluation' => ['required', 'date'],
            'date_prochaine'  => ['nullable', 'date', 'after:date_evaluation'],
            'type'            => ['required', 'in:annuelle,semestrielle,periode_essai,autre'],
            'objectifs_fixes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employe_id.required'      => 'L\'employé est obligatoire.',
            'date_evaluation.required' => 'La date d\'évaluation est obligatoire.',
            'type.required'            => 'Le type d\'évaluation est obligatoire.',
        ];
    }
}
