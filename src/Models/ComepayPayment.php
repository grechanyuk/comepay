<?php

namespace Grechanyuk\Comepay\Models;

use Illuminate\Database\Eloquent\Model;

class ComepayPayment extends Model
{
    protected $fillable = ['order_id', 'payment_id', 'status'];
}
