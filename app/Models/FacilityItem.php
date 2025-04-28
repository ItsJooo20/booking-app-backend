<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityItem extends Model
{
    use HasFactory;

    protected $table = 'facility_items';

    public $timestamps = false;

    protected $fillable = [
        'facility_id',
        'item_code',
        'status',
        // 'is_borrowed',
        'notes',
    ];

    protected $casts = [
        'is_borrowed' => 'boolean',
    ];
    
    // Di model Booking
    public function facilityItem()
    {
        return $this->belongsTo(FacilityItem::class, 'facility_item_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'facility_item_id');
    }

    public function equipmentRequests()
    {
        return $this->hasMany(BookingEquipmentRequest::class, 'facility_item_id');
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class, 'facility_item_id');
    }
}