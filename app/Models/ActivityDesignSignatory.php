<?php

namespace App\Models;

use App\Enums\ActivityDesignSignatoryGroupStatus;
use App\Enums\ActivityDesignStatus;
use Illuminate\Database\Eloquent\Model;

class ActivityDesignSignatory extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'signatory_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function signatory_group()
    {
        return $this->belongsTo(ActivityDesignSignatoryGroup::class, 'activity_design_signatory_group_id');
    }

    protected static function booted()
    {
        static::updated(function (ActivityDesignSignatory $signatory) {
            $signatory_group = $signatory->signatory_group;
            if (! $signatory_group->signatories()->where('is_approved', false)->exists() && $signatory_group->status == ActivityDesignSignatoryGroupStatus::IN_APPROVAL) {
                $signatory_group->update(['status' => ActivityDesignSignatoryGroupStatus::APPROVED]);
                $signatory_group->activity_design
                    ->signatory_groups()
                    ->where('order', '>', $signatory_group->order)
                    ->where('status', ActivityDesignSignatoryGroupStatus::WAITING)
                    ->first()
                    ?->update(['status' => ActivityDesignSignatoryGroupStatus::IN_APPROVAL]);
            }
            if (!$signatory_group->activity_design->signatory_groups()->whereRelation('signatories', 'is_approved', false)->exists()) {
                $signatory_group->activity_design->update([
                    'status' => ActivityDesignStatus::APPROVED
                ]);
            }
        });
    }
}
