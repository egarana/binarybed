<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class RootBucketPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     * Returns empty string to store files directly in root bucket.
     */
    public function getPath(Media $media): string
    {
        return '';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     * Returns empty string since conversions are disabled.
     */
    public function getPathForConversions(Media $media): string
    {
        return '';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     * Returns empty string since responsive images are not used.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return '';
    }
}
