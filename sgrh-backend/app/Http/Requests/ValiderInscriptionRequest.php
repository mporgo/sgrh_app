<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValiderInscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'action'      => ['required', 'in:valider,refuser'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ];
    }
}
