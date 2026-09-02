<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', [], $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $dirty);
            $model->logAudit('updated', $old, $dirty);
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAuditableAttributes(), []);
        });
    }

    protected function logAudit(string $action, array $old, array $new): void
    {
        $hidden = $this->getHidden();
        $excluded = array_merge($hidden, ['password', 'remember_token', 'two_factor_secret']);

        $old = array_diff_key($old, array_flip($excluded));
        $new = array_diff_key($new, array_flip($excluded));

        if ($action === 'updated' && empty($old) && empty($new)) {
            return;
        }

        AuditLog::create([
            'admin_user_id' => auth()->id(),
            'target_user_id' => $this->getUserIdForAudit(),
            'action_type' => $action,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'description' => class_basename($this) . " {$action}",
            'old_values' => empty($old) ? null : $old,
            'new_values' => empty($new) ? null : $new,
            'ip_address' => request()?->ip(),
        ]);
    }

    protected function getAuditableAttributes(): array
    {
        $hidden = $this->getHidden();
        $excluded = array_merge($hidden, ['password', 'remember_token', 'two_factor_secret']);

        return array_diff_key($this->attributesToArray(), array_flip($excluded));
    }

    protected function getUserIdForAudit(): ?int
    {
        if ($this instanceof \App\Models\User) {
            return $this->getKey();
        }

        if (method_exists($this, 'user')) {
            return $this->user_id ?? null;
        }

        return null;
    }
}
