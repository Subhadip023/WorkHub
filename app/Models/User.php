<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* ---------------- Relationships ---------------- */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /* ---------------- Helpers ---------------- */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
