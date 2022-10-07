<?php
namespace App\Http\Controllers;
// set_time_limit(180);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Kfirba\Directo\Directo;

use Aws\S3\S3Client;
use Aws\Sdk;
use Config;

class WelcomeController extends Controller {
    
    public function index() {
        $url = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $videos = [];
        $files = Storage::disk('s3')->files('videos');

        foreach ($files as $file) {
            $videos[] = [
                'name' => str_replace('videos/', '', $file),
                'src' => $url . $file
            ];
        }
        // $directo = new Directo(env('AWS_BUCKET'), env('AWS_DEFAULT_REGION'), env('AWS_ACCESS_KEY_ID'), env('AWS_SECRET_ACCESS_KEY'), $options = []);

        $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
        $bucket = Config::get('filesystems.disks.s3.bucket');

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => '344772707_360.mp4'  // file name in s3 bucket which you want to access
        ]);

        $request = $client->createPresignedRequest($command, '+20 minutes');

        // Get the actual presigned-url
        $presignedUrl = (string)$request->getUri();

        return view('welcome', compact('videos'))->with(['presignedUrl' => $presignedUrl ]);
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