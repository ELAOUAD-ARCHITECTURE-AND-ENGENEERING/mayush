<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App\Traits\Auditable;

class Payment extends Model
{
    use PreventDemoModeChanges, Auditable;

    //
}
