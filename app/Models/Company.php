<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'join_code',
        'created_by',
    ];

    /**
     * A company has many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * ✅ A company has many projects
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
