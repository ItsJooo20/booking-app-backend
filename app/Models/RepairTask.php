<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'damage_report_id',
        'technician_id',
        'description',
        'status',
        'repair_notes',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function damageReport()
    {
        return $this->belongsTo(DamageReport::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}