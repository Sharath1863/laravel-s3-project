<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadFile($file, $path = '')
    {
        try {
            if (!$file) {
                Log::error('No file provided to uploadFile service');
                return null;
            }

            // Generate a unique file name
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Store the file in S3
            $filePath = $file->storeAs($path, $fileName, 's3');
            
            if (!$filePath) {
                Log::error('Failed to store file in S3', [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path
                ]);
                return null;
            }

            // Generate CloudFront URL if configured
            if (config('services.cloudfront.url')) {
                return config('services.cloudfront.url') . '/' . $filePath;
            }
            
            // Fallback to S3 URL
            return Storage::disk('s3')->url($filePath);
        } catch (\Exception $e) {
            Log::error('Error in FileUploadService::uploadFile', [
                'error' => $e->getMessage(),
                'file' => $file ? $file->getClientOriginalName() : 'no file'
            ]);
            return null;
        }
    }

    public function deleteFile($path)
    {
        try {
            if (!$path) {
                return false;
            }

            return Storage::disk('s3')->delete($path);
        } catch (\Exception $e) {
            Log::error('Error in FileUploadService::deleteFile', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }
}
