<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityDesignSignatoryGroup extends Model
{
    public function signatories()
    {
        return $this->hasMany(ActivityDesignSignatory::class);
    }
}
