<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DropZoneController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'file' => 'required|file|max:2048',
        ];

        $messages = [
            'required' => 'The file is required',
            'file.max' => 'The :attribute must be under :max.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['err' => $validator->errors()], 400);
        }

        $fileName = $request->file->getClientOriginalName();

        $request->file->move(storage_path('temp'), $fileName);

        return response()->json('File uploaded successfully', 200);
    }

    public function delete($fileName)
    {
        $filePath = storage_path() . '/temp/' . $fileName;

        if (File::exists($filePath)) {
            File::delete($filePath);

            return response()->json(['msg' => 'File deleted'], 200);
        } else {
            return response()->json(['err' => 'File doesn\'t exist'], 400);
        }
    }
}
