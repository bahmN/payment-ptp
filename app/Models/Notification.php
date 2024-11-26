<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $primaryKey = 'invoice_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'email',
        'is_options',
        'is_stopped',
        'time_of_purchase',
        'is_notificated',
    ];
}
