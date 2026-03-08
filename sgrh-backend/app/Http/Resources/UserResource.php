<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'avatar'         => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'date_naissance' => $this->date_naissance?->format('Y-m-d'),
            'genre'          => $this->genre,
            'adresse'        => $this->adresse,
            'is_active'      => $this->is_active,
            'roles'          => $this->getRoleNames(),        // ['admin', 'rh', ...]
            'permissions'    => $this->getAllPermissions()->pluck('name'),
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
