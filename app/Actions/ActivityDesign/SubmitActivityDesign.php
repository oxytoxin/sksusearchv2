<?php

namespace App\Actions\ActivityDesign;

use App\Enums\ActivityDesignSignatoryGroupStatus;
use App\Enums\ActivityDesignStatus;
use App\Models\ActivityDesign;

class SubmitActivityDesign
{
    public static function execute(ActivityDesign $activity_design)
    {
        $activity_design->update(['status' => ActivityDesignStatus::IN_APPROVAL->value]);
        $activity_design->signatory_groups()->update(['status' => ActivityDesignSignatoryGroupStatus::WAITING]);
        $activity_design->signatory_groups()->first()->update(['status' => ActivityDesignSignatoryGroupStatus::IN_APPROVAL]);
    }
}
