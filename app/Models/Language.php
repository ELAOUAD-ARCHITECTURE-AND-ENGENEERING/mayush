<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
  use PreventDemoModeChanges, HasFactory;
  protected $guarded = [];
}
