<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityCategory extends Model
{
    use HasFactory;

    protected $table = 'facility_category';

    protected $fillable = [
        'name',
        'description',
    ];

    public function facilities()
    {
        return $this->hasMany(Facility::class, 'category_id');
    }
}