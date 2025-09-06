<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function store(Request $request)
    {
        if ($request->hasFile('files')) {
            $uploadedFiles = [];
            foreach ($request->file('files') as $file) {
                $filename = time() . '-' . $file->getClientOriginalName();
                $path = Storage::disk('s3')->putFileAs('uploads', $file, $filename);
                $url = Storage::disk('s3')->url($path);
                $uploadedFiles[] = $url;
            }
            return back()->with('success', 'Files uploaded successfully to S3')->with('files', $uploadedFiles);
        }
        return back()->with('error', 'Please select files to upload');
    }
}
