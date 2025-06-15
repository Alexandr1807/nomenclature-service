<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', $model->getAttributes());
        });

        static::updated(function ($model) {
            $model->logAudit('updated', $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getOriginal());
        });
    }

    protected function logAudit(string $action, array $changes): void
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'entity_type' => $this->getTable(),
            'entity_id'   => $this->getKey(),
            'action'      => $action,
            'changes'     => $changes,
        ]);
    }
}
