<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $table = 'order';

    protected $primaryKey = 'invoice_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'amount',
        'currency',
        'description',
        'lang',
        'email',
        'payment_id',
        'return_url',
        'status',
        'customer_ip',
        'date',
        'operation_id'
    ];
}
