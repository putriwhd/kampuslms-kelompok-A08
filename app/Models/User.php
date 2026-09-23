<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
<<<<<<< HEAD
use App\Models\Course;
=======
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nim_nip',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
<<<<<<< HEAD
     * Relasi User dengan Course yang diajarkan.
=======
     * Relasi ke mata kuliah yang diampu (sebagai Dosen)
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
     */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }
<<<<<<< HEAD
=======

    /**
     * Relasi ke mata kuliah yang diikuti (sebagai Mahasiswa)
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }

    /**
     * Relasi ke tugas yang dikumpulkan
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Relasi ke nilai yang diberikan (sebagai Penilai/Dosen)
     */
    public function gradesGiven(): HasMany
    {
        return $this->hasMany(Grade::class, 'graded_by');
    }
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
}