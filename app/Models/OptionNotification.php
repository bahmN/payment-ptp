<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionNotification extends Model {
    protected $table = 'options_notifiaction';

    public $timestamps = false;

    protected $fillable = [
        'message',
        'time_of_sending',
        'is_active',
        'is_options',
        'uri_picture'
    ];
}
