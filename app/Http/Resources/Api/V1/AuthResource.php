<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $action = $this['action'] ?? 'login';  // ← USE ACTION TYPE
        
        return [
            'message' => $action === 'register' 
                ? 'Registration successful' 
                : 'Login successful',  // ← CLEAR DISTINCTION
            'user' => new UserResource($this['user']),
            'token' => $this['token'],
            'company' => $this['company'],
            ...(isset($this['status']) ? ['status' => $this['status']] : [])  // ← REGISTER ONLY
        ];
    }
}
