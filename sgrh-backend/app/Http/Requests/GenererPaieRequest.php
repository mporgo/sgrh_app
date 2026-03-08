<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenererPaieRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'  => ['required', 'exists:employes,id'],
            'mois'        => ['required', 'integer', 'min:1', 'max:12'],
            'annee'       => ['required', 'integer', 'min:2020', 'max:2100'],
            'elements'    => ['nullable', 'array'],
            'elements.*.libelle'    => ['required_with:elements', 'string'],
            'elements.*.type'       => ['required_with:elements', 'in:prime,deduction,avantage'],
            'elements.*.montant'    => ['required_with:elements', 'numeric', 'min:0'],
            'elements.*.commentaire'=> ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employe_id.required' => 'L\'employé est obligatoire.',
            'mois.required'       => 'Le mois est obligatoire.',
            'annee.required'      => 'L\'année est obligatoire.',
        ];
    }
}
