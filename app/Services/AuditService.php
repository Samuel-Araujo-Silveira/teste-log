<?php

namespace App\Services;

use App\Repositories\AuditLogRepository;

class AuditService
{
    private $auditLogRepository;

    public function __construct()
    {
        $this->auditLogRepository = new AuditLogRepository();
    }

    /**
     * Registra o fato auditável no banco: quem fez, o que fez,
     * em qual entidade, com que resultado e sob qual correlation ID.
     * Diagnóstico técnico não entra aqui.
     */
    public function register($action, $result, $subjectType = null, $subjectId = null, array $metadata = [])
    {
        return $this->auditLogRepository->add([
            'correlation_id' => request()->correlation_id,
            'user_id' => request()->user_id,
            'establishment_id' => request()->establishment_id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'result' => $result,
            'metadata' => empty($metadata) ? null : json_encode($metadata),
        ]);
    }
}
