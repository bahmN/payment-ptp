<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model {
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'end_life',
    ];
}
