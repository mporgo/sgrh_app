<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Infos User
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'date_naissance'  => ['nullable', 'date', 'before:today'],
            'genre'           => ['nullable', 'in:M,F,autre'],
            'adresse'         => ['nullable', 'string'],
            // Infos Employe
            'departement_id'  => ['nullable', 'exists:departements,id'],
            'poste_id'        => ['nullable', 'exists:postes,id'],
            'manager_id'      => ['nullable', 'exists:users,id'],
            'date_embauche'   => ['required', 'date'],
            'type_contrat'    => ['required', 'in:CDI,CDD,Stage,Freelance'],
            'fin_contrat'     => ['nullable', 'date', 'after:date_embauche'],
            'salaire_base'    => ['required', 'numeric', 'min:0'],
            'conge_solde'     => ['nullable', 'integer', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Le nom est obligatoire.',
            'email.required'        => 'L\'email est obligatoire.',
            'email.unique'          => 'Cet email est déjà utilisé.',
            'date_embauche.required'=> 'La date d\'embauche est obligatoire.',
            'type_contrat.required' => 'Le type de contrat est obligatoire.',
            'salaire_base.required' => 'Le salaire de base est obligatoire.',
        ];
    }
}
