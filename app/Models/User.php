<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'permissions',
        'device_token',
        'study_streak',
        'coins',
        'badges',
        'daily_audio_minutes_listened',
        'locked_cooldown_until',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_cooldown_until' => 'datetime',
            'password' => 'hashed',
            'study_streak' => 'integer',
            'coins' => 'integer',
            'badges' => 'array',
            'permissions' => 'array',
            'daily_audio_minutes_listened' => 'integer',
        ];
    }

    public function courseProgress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
