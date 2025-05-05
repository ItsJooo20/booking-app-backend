<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facility_item_id',
        'start_datetime',
        'end_datetime',
        'purpose',
        'status',
        // 'headmaster_approved',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'headmaster_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facilityItem()
    {
        return $this->belongsTo(FacilityItem::class, 'facility_item_id');
    }

    

    // Check if booking is currently active
    public function isActive()
    {
        $now = now();
        return $this->start_datetime <= $now && $this->end_datetime >= $now && $this->status === 'approved';
    }
}