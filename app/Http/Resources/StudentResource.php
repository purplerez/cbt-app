<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'name' => $this->name,
            'gender' => $this->gender,
            'grade' => [
                'id' => $this->grade->id,
                'name' => $this->grade->name,
            ],
            'school_id' => $this->school_id,
            'is_assigned' => $this->is_assigned ?? false,
            'user' => $this->when($this->user, [
                'id' => $this->user?->id,
                'email' => $this->user?->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
