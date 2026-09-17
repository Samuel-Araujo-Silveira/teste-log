<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'correlation_id',
        'user_id',
        'establishment_id',
        'action',
        'subject_type',
        'subject_id',
        'result',
        'metadata',
    ];
}
