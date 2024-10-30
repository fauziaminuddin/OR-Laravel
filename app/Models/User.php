<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
    public function assets()
    {
        return $this->hasMany(UserAsset::class);
    }
    public function service()
    {
        return $this->hasMany(ServiceUser::class);
    }
    public function attributeDashboards()
    {
        return $this->hasMany(AttributeDashboard::class);
    }
    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
    public function collaborators()
    {
        return $this->hasMany(Collaborator::class);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
}
