<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Model;

class PaymentInformation extends Model
{
    use PreventDemoModeChanges;

    protected $table = 'payment_informations';

    protected $guarded = [];

    protected $casts = [
        'set_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBankNameTextAttribute(): ?string
    {
        return $this->bank_name;
    }

    public function getPaymentNameTextAttribute(): ?string
    {
        return $this->payment_name;
    }
}
