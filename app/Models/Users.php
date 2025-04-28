<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Users extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $primaryKey = 'id';
    protected $table = 'users';
    protected $fillable = ["name", "email", "password", "role", "phone", "remember_token", "is_active", "created_at", "updated_at", "deleted_at"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->password = Hash::make($user->password);
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class, 'reporter_id');
    }

    public function repairTasks()
    {
        return $this->hasMany(RepairTask::class, 'technician_id');
    }
}
