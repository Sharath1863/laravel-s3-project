<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function store(Request $request)
    {
        try {
            // Check if a file was actually uploaded
            if (!$request->hasFile('file')) {
                return back()->with('error', 'No file was uploaded. Please select a file.');
            }

            $file = $request->file('file');

            // Validate file before processing
            if (!$file->isValid()) {
                return back()->with('error', 'File upload failed. The file may be corrupted or too large.');
            }

            // Validate file type and size
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:jpeg,jpg,png,gif,pdf,doc,docx',
                    'max:20480', // 20MB Max
                ]
            ], [
                'file.required' => 'Please select a file to upload.',
                'file.mimes' => 'Only images (JPG, PNG, GIF), PDFs, and Word documents are allowed.',
                'file.max' => 'File size must not exceed 20MB.',
            ]);
            }

            // Generate a unique filename
            $fileName = Str::uuid() . '_' . $file->getClientOriginalName();
            
            try {
                // Ensure we're using s3 disk
                config(['filesystems.default' => 's3']);
                
                // Log the attempt
                \Log::info('Attempting S3 upload', [
                    'filename' => $fileName,
                    'disk' => config('filesystems.default'),
                    'bucket' => config('filesystems.disks.s3.bucket')
                ]);

                // Store file in S3
                $path = Storage::putFileAs(
                    'uploads', 
                    $file, 
                    $fileName, 
                    ['visibility' => 'public']
                );

                if (!$path) {
                    throw new \Exception('Failed to upload file to S3');
                }

                // Get the URL
                $url = Storage::url($path);

                // Log success
                \Log::info('File uploaded successfully', [
                    'path' => $path,
                    'url' => $url
                ]);

                return back()
                    ->with('success', 'File uploaded successfully!')
                    ->with('url', $url);

            } catch (\Exception $e) {
                \Log::error('Upload error', [
                    'error' => $e->getMessage(),
                    'file' => $fileName
                ]);
                
                return back()->with('error', 'Failed to upload file: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Validation error', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error validating file: ' . $e->getMessage());
        }
    }
}
