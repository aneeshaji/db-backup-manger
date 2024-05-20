<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class FileUploadController extends Controller
{
    public function getFileUploadForm()
    {
        return view('file-upload');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:doc,csv,txt,png,svg,pdf,jpeg,xls,doc|max:2048',
        ]);
 
        $fileName = $request->file->getClientOriginalName();
        $filePath = $fileName;

        Storage::disk('s3')->put($filePath, file_get_contents($request->file), 'public');
        $urlPath = Storage::disk('s3')->url($filePath);

        if ($urlPath) {
            return back()
            ->with('success','File has been successfully uploaded.');
        } else {
            return back()
            ->with('error','Upload failed!');
        }
    }
}