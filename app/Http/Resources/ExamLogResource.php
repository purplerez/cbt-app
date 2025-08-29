<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamLogResource extends JsonResource
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
            'session_id' => $this->session_id,
            'action' => $this->action,
            'details' => $this->details,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'device' => $this->device,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->examsession->user->id,
            ],
        ];
    }
}
