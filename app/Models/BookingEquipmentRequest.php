<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingEquipmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'facility_item_id',
        'status',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function facilityItem()
    {
        return $this->belongsTo(FacilityItem::class, 'facility_item_id');
    }
}
