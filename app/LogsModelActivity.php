<?php

namespace App;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->logOnly($this->getFillable())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $modelName = class_basename($this);
                $identifier = $this->getLogIdentifier();

                $causerUser = auth()->user();
                $causerName = $causerUser?->name ?? 'System';
                $causerEmail = $causerUser?->email ?? 'system@local';
                $causer = "{$causerName} ({$causerEmail})";

                return match ($eventName) {
                    'created' => "{$modelName} <span class=\"font-semibold\">{$identifier}</span> was created by <span class=\"font-semibold\">{$causer}</span>.",
                    'updated' => "{$modelName} <span class=\"font-semibold\">{$identifier}</span> was updated by <span class=\"font-semibold\">{$causer}</span>. Changes: " .
                        collect($this->getChanges())
                            ->except(['updated_at', 'created_at'])
                            ->map(function ($new, $key) {
                                if ($key === 'password') {
                                    return "<span class=\"font-semibold\">password</span> was changed.";
                                }
                                return "<span class=\"font-semibold\">{$key}:</span> \"{$this->getOriginal($key)}\" → \"{$new}\".";
                            })
                            ->implode(', '),
                    'deleted' => "{$modelName} <span class=\"font-semibold\">{$identifier}</span> was deleted by <span class=\"font-semibold\">{$causer}</span>.",
                    default => "{$modelName} <span class=\"font-semibold\">{$identifier}</span> had event <span class=\"font-semibold\">{$eventName}</span> by <span class=\"font-semibold\">{$causer}</span>."
                };
            });
    }

    /**
     * Get the identifier to show in log messages
     */
    public function getLogIdentifier(): string
    {
        return $this->name ?? $this->title ?? (string) $this->id;
    }
}
