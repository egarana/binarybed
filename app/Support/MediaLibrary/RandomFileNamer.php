<?php

namespace App\Support\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class RandomFileNamer extends FileNamer
{
    /**
     * Generate a UUID-based filename for better security and standardization.
     * UUID v4 provides guaranteed uniqueness and is industry standard.
     * Note: Spatie Media Library automatically appends the extension,
     * so we only return the base filename without extension.
     */
    public function originalFileName(string $fileName): string
    {
        // Use UUID v4 for guaranteed uniqueness and security
        return (string) Str::uuid();
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return $fileName;
    }

    public function responsiveFileName(string $fileName): string
    {
        return $fileName;
    }
}
