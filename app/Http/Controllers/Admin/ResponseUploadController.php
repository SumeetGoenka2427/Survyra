<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseUpload;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseUploadController extends Controller
{
    public function download(ResponseUpload $upload): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($upload->stored_path), 404);

        return Storage::disk('local')->download($upload->stored_path, $upload->original_name);
    }
}
