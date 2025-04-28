<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'facility_item_id',
        'title',
        'description',
        'status',
        'image_path',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function facilityItem()
    {
        return $this->belongsTo(FacilityItem::class, 'facility_item_id');
    }

    public function repairTask()
    {
        return $this->hasOne(RepairTask::class);
    }
}