<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('employe')?->user_id;

        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'email'          => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'phone'          => ['nullable', 'string', 'max:20'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'genre'          => ['nullable', 'in:M,F,autre'],
            'adresse'        => ['nullable', 'string'],
            'departement_id' => ['nullable', 'exists:departements,id'],
            'poste_id'       => ['nullable', 'exists:postes,id'],
            'manager_id'     => ['nullable', 'exists:users,id'],
            'date_embauche'  => ['sometimes', 'date'],
            'type_contrat'   => ['sometimes', 'in:CDI,CDD,Stage,Freelance'],
            'fin_contrat'    => ['nullable', 'date', 'after:date_embauche'],
            'salaire_base'   => ['sometimes', 'numeric', 'min:0'],
            'conge_solde'    => ['nullable', 'integer', 'min:0'],
            'statut'         => ['sometimes', 'in:actif,inactif,suspendu'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
