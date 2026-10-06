<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'sks' => $this->sks,
            'lecturer_id' => $this->lecturer_id,
            'status' => $this->status,
            'counts' => [
                'materials' => $this->whenCounted('materials'),
                'assignments' => $this->whenCounted('assignments'),
            ],
            'created_at' => $this->created_at?->toIso8601String(),

            'lecturer' => new UserResource(
                $this->whenLoaded('lecturer')
            ),

            'students' => UserResource::collection(
                $this->whenLoaded('students')
            ),

            'materials' => MaterialResource::collection(
                $this->whenLoaded('materials')
            ),

            'assignments' => AssignmentResource::collection(
                $this->whenLoaded('assignments')
            ),
        ];
    }
}
