<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

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

    public function isCompanyAdmin(): bool
{
    return $this->company && $this->company->created_by === $this->id;
}

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

