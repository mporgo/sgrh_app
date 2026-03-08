<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaieRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'elements'    => ['nullable', 'array'],
            'elements.*.libelle'     => ['required_with:elements', 'string'],
            'elements.*.type'        => ['required_with:elements', 'in:prime,deduction,avantage'],
            'elements.*.montant'     => ['required_with:elements', 'numeric', 'min:0'],
            'elements.*.commentaire' => ['nullable', 'string'],
            'notes'          => ['nullable', 'string'],
            'date_paiement'  => ['nullable', 'date'],
        ];
    }
}
