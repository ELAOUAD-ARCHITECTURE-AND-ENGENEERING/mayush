<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessSetting extends Model
{
    use PreventDemoModeChanges, HasFactory;

    protected $fillable = ['type', 'value', 'lang'];
}
