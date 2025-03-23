<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ActivityDesign extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function signatory_groups()
    {
        return $this->hasMany(ActivityDesignSignatoryGroup::class);
    }

    public function particulars()
    {
        return $this->hasMany(ActivityDesignParticular::class);
    }

    public function participants()
    {
        return $this->hasMany(ActivityDesignParticipant::class);
    }

    public function coordinators()
    {
        return $this->hasMany(ActivityDesignCoordinator::class);
    }

    protected static function booted()
    {
        static::created(function ($activityDesign) {
            $activityDesign->total_amount = $activityDesign->particulars()->sum('amount');
            $activityDesign->save();
        });
    }
}
