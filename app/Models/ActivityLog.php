<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperActivityLog
 */
class ActivityLog extends Model
{
    use HasFactory;

    public function loggable()
    {
        return $this->morphTo();
    }
}
