<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
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
            'course_id' => $this->course_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'due_at' => $this->due_at,
            'max_score' => $this->max_score,
            'allow_late' => $this->allow_late,
            'status' => $this->status,

            'course' => new CourseResource(
                $this->whenLoaded('course')
            ),

            'creator' => new UserResource(
                $this->whenLoaded('creator')
            ),

            'submissions' => SubmissionResource::collection(
                $this->whenLoaded('submissions')
            ),
        ];
    }
}