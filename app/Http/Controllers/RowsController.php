<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Jobs\ProcessRowsFile;
use App\Models\Row;
use Illuminate\Http\Request;

class RowsController extends Controller
{

    public function upload(UploadRequest $request)
    {
        $file = $request->file('rowsfile')->store('rowfiles');
        ProcessRowsFile::dispatch($file);
        return response('Success.');
    }

    public function index()
    {
        return response()->json(
            Row::get()->groupBy('date')
        );
    }
}
