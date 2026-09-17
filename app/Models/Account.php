<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'uuid',
        'name',
        'establishment_id',
        'status_account_id',
        'total',
    ];
}
