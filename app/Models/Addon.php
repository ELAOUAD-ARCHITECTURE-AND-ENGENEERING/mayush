<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Addon extends Model
{
    use PreventDemoModeChanges;

    protected $fillable = ['unique_identifier', 'name', 'activated', 'image', 'purchase_code', 'version'];
}
