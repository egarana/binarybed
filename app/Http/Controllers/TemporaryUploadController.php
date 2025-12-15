<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemporaryUploadController extends Controller
{
    /**
     * Upload a file to temporary storage.
     * 
     * The file is attached to a TemporaryUpload model and will be
     * moved to the final model when the form is submitted.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // 10MB
            'collection' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $file = $request->file('file');
            $collection = $request->input('collection', 'default');

            // Create temporary upload record
            $tempUpload = TemporaryUpload::create([
                'session_id' => $request->session()->getId(),
                'collection_name' => $collection,
            ]);

            // Add media to the temporary upload
            $media = $tempUpload->addMedia($file)
                ->toMediaCollection('default');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempUpload->id,
                    'media_id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->file_name,
                    'size' => $media->size,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Temporary upload failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('file')?->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a temporary upload.
     * 
     * This is called when user removes a file before submitting the form.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tempUpload = TemporaryUpload::where('id', $id)
            ->where('session_id', $request->session()->getId())
            ->first();

        if (!$tempUpload) {
            return response()->json([
                'success' => false,
                'message' => 'Upload not found.',
            ], 404);
        }

        try {
            // Delete media and the temporary upload record
            $tempUpload->clearMediaCollection('default');
            $tempUpload->delete();

            return response()->json([
                'success' => true,
                'message' => 'Upload deleted.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete temporary upload', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete upload.',
            ], 500);
        }
    }
}
