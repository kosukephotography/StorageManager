<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpportunityRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_id',
        'opportunity_id',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

    public function createdByUser()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'created_by');
    }

    public function updatedByUser()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'updated_by');
    }
}