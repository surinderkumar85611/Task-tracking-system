<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function upload(Request $request)
    {
        if (!$request->hasFile('upload')) {
            return response()->json([
                'error' => [
                    'message' => 'No file uploaded'
                ]
            ], 400);
        }

        $file = $request->file('upload');

        $path = $file->store('attachments', 'public');

        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }
}