<?php

namespace App\Repositories;

use App\Interfaces\DefaultRepositoryInterface;
use App\Models\AuditLog;

class AuditLogRepository extends BaseRepository implements DefaultRepositoryInterface
{
    public function __construct()
    {
        $this->prepare();
    }

    public function prepare()
    {
        $this->query = AuditLog::query();
    }

    public function filteredByCorrelationId($correlationId)
    {
        $this->query->where('correlation_id', $correlationId);

        return $this;
    }
}
