<?php

namespace App\Listeners;

use App\Events\MaterialChanged;
use App\Notifications\AdminActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendMaterialChangedNotification
{
    public function handle(MaterialChanged $event): void
    {
        $material = $event->material->loadMissing(['course', 'uploader']);
        if ($material->uploader && $material->uploader->role !== 'dosen') {
            return;
        }
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        $message = $event->action === 'updated'
            ? 'Dosen telah memperbarui materi pada mata kuliah ' . $material->course->name . '.'
            : 'Dosen telah mengunggah materi baru pada mata kuliah ' . $material->course->name . '.';

        Notification::send($admins, new AdminActivityNotification(
            $event->action === 'updated' ? 'Materi diperbarui' : 'Materi baru diunggah',
            $message,
            route('admin.courses.materials.index', [
                'course' => $material->course,
                'as' => 'admin',
            ], false)
        ));
    }
}
