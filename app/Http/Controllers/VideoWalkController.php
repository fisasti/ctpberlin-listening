<?php

namespace App\Http\Controllers;


use App\VideoWalk;
use App\Library\Misc;
use Aws\S3\S3Client;
use Aws\Sdk;
use GuzzleHttp;
use MuxPhp;
use MuxPhp\Api\PlaybackIDApi;
use Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class VideoWalkController extends Controller {
    var $bucketPublicBase = 'https://listeningtothecity.s3.eu-central-1.amazonaws.com/videos/';
    // var $mux = array('accessToken' => '21c97388-577c-42b2-8abe-c168dc54e437',
    // 'secret' => 'x1Y2SX1n9zII6Fr9fgefZPl/HFxKEZ+kmLIH0ySmw/X/DKyvJQeXdXH0aJ1s0xtNYjuzGAoRWxq');

    public function add(Request $request) {
        // ffmpeg -i $amazon_s3_signed_url -ss 00:00:00.500 -vframes 1 thumbnail.png
        // https://stackoverflow.com/questions/23640869/create-thumbnail-from-video-file-via-file-input
        // https://stackoverflow.com/questions/13198131/how-to-save-an-html5-canvas-as-an-image-on-a-server
        $city = $request->city ?? false;
        $name = $request->name ?? false;
        $extension = $request->extension ?? false;
        $result = array('state' => 'error');

        if ($city && $name && $extension) {
            $videoWalk = new VideoWalk;
            $videoWalk->city = $city;
            $videoWalk->name = $name;
            $videoWalk->save();

            $filename = $videoWalk->id . '-' . $this->sanitizeFileName($city) . '.' . $extension;
            $presignedUrl = $this->buildAWSPresignedURL($filename);

            $videoWalk->videoUrl = $this->bucketPublicBase . $filename;
            $videoWalk->save();

            $result['id'] = $videoWalk->id;
            $result['videoUploadData'] = $presignedUrl;
            $result['state'] = 'ok';
        }

        return response()->json($result);
    }

    public function webhookMuxAssetCreated(Request $request) {
        if ($request['type'] == 'video.asset.ready') {
            $muxId = $request['data']['id'];
            $playbackId = $request['data']['playback_ids'][0]['id'];
            $videoDuration = $request['data']['duration'];
            $videoWalk = VideoWalk::where('muxId', $muxId)->firstOrFail();

            $videoWalk->streamUrl = 'https://stream.mux.com/' . $playbackId . '.m3u8';
            $videoWalk->state = 3;
            $videoWalk->save();
            
            $this->downloadMuxThumb($videoWalk->id, $playbackId);
            $this->generateSubs($videoWalk->id);
            
            Log::info($muxId);
            Log::info($playbackId);
            Log::info($videoDuration);
            Log::info($request);
        }
    }

    public function transcodeVideo(Request $request) {
        $id = $request->has('id') ? intval($request->id) : -1;
        $videoWalk = VideoWalk::findOrFail($id);
        $hasThumbnail = Storage::disk('public')->exists('thumbs/' . $id . '.jpg');
        $hasSubtitles = Storage::disk('public')->exists('subs/' . $id . '.vtt');
        $thumbnail = $hasThumbnail ? 'thumbs/' . $id . '.jpg' : '';
        $videoDuration = -1;
        $streamUrl = '';
        $subtitlesUrl = '';
        $status = '';

        $config = MuxPhp\Configuration::getDefaultConfiguration()
        ->setUsername(getenv('MUX_TOKEN_ID'))
        ->setPassword(getenv('MUX_TOKEN_SECRET'));

        $assetsApi = new MuxPhp\Api\AssetsApi(new GuzzleHttp\Client(), $config);
        $playbackIdApi = new MuxPhp\Api\PlaybackIDApi(new GuzzleHttp\Client(), $config);

        if (empty($videoWalk->muxId)) {
            $videoWalk->state = 1; // VIDEO UPLOADED
            $videoWalk->save();

            $input = new MuxPhp\Models\InputSettings(["url" => $videoWalk->videoUrl]);
            $createAssetRequest = new MuxPhp\Models\CreateAssetRequest(["input" => [$input], "playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC] ]);
            $result = $assetsApi->createAsset($createAssetRequest);

            $status = $result->getData()->getStatus();
            $videoWalk->muxId = $result->getData()->getId();

            if ($status == 'ready') {
                $playbackId = $result->getData()->getPlaybackIds()[0]->getId();
                $videoDuration = floor($result->getData()->getDuration());
                $streamUrl = 'https://stream.mux.com/' . $playbackId . '.m3u8';
                $videoWalk->state = 3;
                $videoWalk->streamUrl = $streamUrl;
            }
            else
                $videoWalk->state = 2;

            $videoWalk->save();
        }
        elseif ($videoWalk->state < 3) {
            $asset = $assetsApi->getAsset($videoWalk->muxId);
            $status = $asset->getData()->getStatus();

            if ($status == 'ready' && empty($videoWalk->streamUrl)) {
                $playbackId = $asset->getData()->getPlaybackIds()[0]->getId();
                $streamUrl = 'https://stream.mux.com/' . $playbackId . '.m3u8';
                $videoDuration = floor($asset->getData()->getDuration());
                $videoWalk->state = 3;
                $videoWalk->streamUrl = $streamUrl;
                $videoWalk->save();
            }       
        }
        else { // VIDEO IS READY
            $status = 'ready';
            $streamUrl = $videoWalk->streamUrl;
        }
        
        if (!$hasSubtitles) { // SUBTITLE GENERATION
            if ($videoDuration == -1) {
                $asset = $assetsApi->getAsset($videoWalk->muxId);
                $videoDuration = floor($asset->getData()->getDuration());
            }
            // print $videoDuration;
            $subtitlesUrl = $this->generateSubs($id);
        }

        if (!$hasThumbnail && !empty($streamUrl)) {
            $playbackId = str_replace(array('https://stream.mux.com/', '.m3u8'), '', $streamUrl);
            $this->downloadMuxThumb($id, $playbackId);
        }

        return response()->json(['streamUrl' => $streamUrl,
        'status' => $status,
        'subtitlesUrl' => $subtitlesUrl,
        'thumbnail' => $thumbnail]);
    }

    function downloadMuxThumb($videoId, $playbackId) {
        $url = 'https://image.mux.com/' . $playbackId . '/thumbnail.jpg';

        $thumbnail = 'thumbs/' . $videoId . '.jpg';
        Storage::disk('public')->put($thumbnail, file_get_contents($url));
    }

    public function generateSubs($videoId) {
        $masterSubs = Storage::disk('local')->get('masterSubs.txt');
        $videoWalk = VideoWalk::findOrFail($videoId);
        $masterSubs = str_replace('CITYNAME', $videoWalk->city, $masterSubs);
        $masterParagraphs = explode("\r\n\r\n", $masterSubs);
        $lineBreak = "\r\n";
        $subtitle = "";

        $paragraphs = array();
        $startLength = 10;
        $endLength = 8; // including last paragraph ending, 5 + 3
        $videoLength = 600;
        $totalParagraphs = 15;
        
        $paragraphDuration = number_format(($videoLength - $startLength - $endLength) / $totalParagraphs, 3);
        $totalRest = ($videoLength - $startLength - $endLength) % $totalParagraphs;

        // print "Paragraph Duration: $paragraphDuration. Rest: $totalRest<br />";
        $endingPhrase = "LISTENING TO THE CITY AS A FORM OF WRITING\r\n" . date("Y");

        // First two paragraphs and last one are fixed
        $paragraphs[] = $masterParagraphs[0];
        $paragraphs[] = $masterParagraphs[1];
        $lastParagraph = $masterParagraphs[count($masterParagraphs)-1];
        
        $masterParagraphs = array_slice($masterParagraphs, 2, count($masterParagraphs)-3);
        shuffle($masterParagraphs);

        for ($i=3,$k=$i-3;$i<=$totalParagraphs-1 && isset($masterParagraphs[$k]);$i++,$k++) {
            $paragraphs[] = $masterParagraphs[$k];
        }

        // 00:01.000 --> 00:04.000
        $paragraphs[] = $lastParagraph;
        
        // SUBTITLE CREATION
        $currentTime = $startLength . '.000';
        $subtitle = "WEBVTT" . $lineBreak . $lineBreak;
        // print "Paragraph duration: $paragraphDuration<br /><br />";

        foreach ($paragraphs as $paragraph) {
            $lines = explode("\r\n", $paragraph);
            $silenceLength = rand(3,6);
            $lineTime = number_format(($paragraphDuration - $silenceLength) / count($lines), 3);
            // print "Lineas: " . count($lines) . ". LineTime: $lineTime. Silence: $silenceLength<br />";

            foreach ($lines as $line) {
                $comma = '0.' . rand(200,400);
                $to = number_format($currentTime + $lineTime - $comma, 3);
                // print "CurrentTime: $currentTime. Linetime: " . $lineTime . ". Comma: $comma. To: $to<br />";
                $fromTo = $this->vttFromTo($currentTime,$to);
                
                $subLines = $fromTo['from'] . " --> " . $fromTo['to'] . $lineBreak;
                $subLines .= $line . $lineBreak . $lineBreak;

                $subtitle .= $subLines;
                // print nl2br($subLines);
                $currentTime = number_format($currentTime + $lineTime, 3);
            }
            $currentTime = number_format($currentTime + $silenceLength, 3);
            // print "<br /><br /><br />";
        }
        
        // ENDING PHRASE
        $currentTime = $videoLength - 4;
        $fromTo = $this->vttFromTo($currentTime . '.000',($currentTime+3) . '.000');

        $subLines = $fromTo['from'] . " --> " . $fromTo['to'] . $lineBreak;
        $subLines .= $endingPhrase . $lineBreak . $lineBreak;
        // print nl2br($subLines);
        $subtitle .= $subLines;

        Storage::disk('public')->put('subs/' . $videoId . '.vtt', $subtitle);

        return Storage::url('subs/' . $videoId . '.vtt');
        // print nl2br($subtitle);
        // foreach ($paragraphs as $paragraph) print str_replace("\r\n", '<br />', $paragraph) . "<br /><br />";
    }

    private function vttFromTo($from, $to) {
        // print "From: $from. To: $to<br />";
        $f = explode('.', $from);
        $t = explode('.', $to);
        $fromMil = $f[1];
        $toMil = $t[1];
        $from = $f[0];
        $to = $t[0];
        
        $fromMin = floor($from / 60);
        $fromSec = $from % 60;

        $toMin = floor($to / 60);
        $toSec = $to % 60;

        // print $fromMin . ":" . $fromSec . "." . $fromMil . " " . $toMin . ":" . $toSec . "." . $toMil;
        if ($fromMin < 10)
            $fromMin = '0' . $fromMin;
        
        if ($fromSec < 10)
            $fromSec = '0' . $fromSec;

        if ($toMin < 10)
            $toMin = '0' . $toMin;
        
        if ($toSec < 10)
            $toSec = '0' . $toSec;
        
        $from = $fromMin . ':' . $fromSec . '.' . $fromMil;
        $to = $toMin . ':' . $toSec . '.' . $toMil;

        return ['from' => $from, 'to' => $to];
    }

    private function sanitizeFileName($str) {
        $str = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $str);
        $str = mb_ereg_replace("([\.]{2,})", '', $str);

        return $str;
    }

    private function buildAWSPresignedURL($filename) {
        // $directo = new Directo(env('AWS_BUCKET'), env('AWS_DEFAULT_REGION'), env('AWS_ACCESS_KEY_ID'), env('AWS_SECRET_ACCESS_KEY'), $options = []);

        $s3 = Storage::disk('s3');
        $client = new S3Client([
            'region'  => env('AWS_DEFAULT_REGION'),
            // 'profile' => 'default',
            'version' => '2006-03-01'
        ]);
        $expiry = "+10 minutes";

        /* $cmd = $client->getCommand('PutObject', [
            'Bucket' => \Config::get('filesystems.disks.s3.bucket'),
            'Key' => 'videos/test' . time() . '.mp4',
            'Body' => 'testasdasd'
            // 'Content-Type' => 'multipart/form-data',
            // 'ACL' => 'public-read',
        ]);
        $result = $client->execute($cmd);
        $request = $client->createPresignedRequest($cmd, $expiry); */
        
        $key = 'videos/' . $filename;
        $formInputs = [
            'acl' => 'public-read',
            'key' => $key
        ];
        
        $options = [
            ['bucket' => env('AWS_BUCKET') ],
            ['eq', '$key', $key],
            ['acl' => 'public-read']
        ];

        $postObject = new \Aws\S3\PostObjectV4(
            $client,
            env('AWS_BUCKET'),
            $formInputs,
            $options,
            $expiry
        );

        // $presignedUrl = (string) $request->getUri();
        $presignedUrl = '';
        $formAttributes = $postObject->getFormAttributes();
        $formInputs = $postObject->getFormInputs();
        // var_dump($formInputs);
        
        return ['presignedUrl' => $presignedUrl, 'action' => $formAttributes['action'],
        'formInputs' => $formInputs ];
    }
}
