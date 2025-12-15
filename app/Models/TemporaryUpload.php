<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Model for handling temporary file uploads.
 * 
 * Files are uploaded immediately when user selects them, attached to this model,
 * then moved to the final model (Unit/Activity) when form is submitted.
 * Orphaned uploads are automatically cleaned up after 24 hours.
 */
class TemporaryUpload extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Use central database connection for temporary uploads.
     * This ensures temporary uploads work in a multi-tenant environment.
     */
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'collection_name',
    ];

    /**
     * Register the media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile();
    }

    /**
     * Scope to filter by session ID.
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to filter old uploads (older than given hours).
     */
    public function scopeOlderThan($query, int $hours = 24)
    {
        return $query->where('created_at', '<', now()->subHours($hours));
    }
}
