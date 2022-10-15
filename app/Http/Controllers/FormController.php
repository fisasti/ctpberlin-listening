<?php
namespace App\Http\Controllers;
// set_time_limit(180);

use App\VideoWalk;
use Aws\S3\S3Client;
use Aws\Sdk;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormController extends Controller {
    public function getVideos() {
        $url = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $videos = [];
        $files = Storage::disk('s3')->files('videos');

        foreach ($files as $file) {
            $videos[] = [
                'name' => str_replace('videos/', '', $file),
                'src' => $url . $file
            ];
        }
    }

    public function index() {
        return view('add-video')->with(['videos' => VideoWalk::where('state', 3)->get()]);
    }   
 
    public function store(Request $request){
        $this->validate($request, [
            'video' => 'required|mimes:mp4,mov,ogg,qt|max:1048576'
        ]);
        
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $name = time() . $file->getClientOriginalName();
            $filePath = 'videos/' . $name;
            Storage::disk('s3')->put($filePath, fopen($file, 'r+'));
        }
        
        return back()->withSuccess('Video uploaded successfully');
    }
 
    public function destroy($image) {
        Storage::disk('s3')->delete('videos/' . $image);
        
        return back()->withSuccess('Image was deleted successfully');
    }
}