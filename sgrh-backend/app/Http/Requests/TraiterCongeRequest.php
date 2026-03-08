<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TraiterCongeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'action'       => ['required', 'in:valider,refuser'],
            'motif_refus'  => ['required_if:action,refuser', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required'         => 'L\'action est obligatoire.',
            'motif_refus.required_if' => 'Le motif de refus est obligatoire.',
        ];
    }
}
