<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB Max
            ]);

            if (!$request->hasFile('file')) {
                return back()->with('error', 'No file was uploaded.');
            }

            $file = $request->file('file');
            if (!$file->isValid()) {
                return back()->with('error', 'File upload failed. Please try again.');
            }

            $url = $this->fileUploadService->uploadFile($file, 'uploads');
            
            if (!$url) {
                return back()->with('error', 'Failed to upload file to storage.');
            }

            return back()->with('success', 'File uploaded successfully!')
                        ->with('files', [$url]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        $deleted = $this->fileUploadService->deleteFile($request->path);

        return response()->json([
            'success' => $deleted
        ]);
    }
}
