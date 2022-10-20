<?php

use Illuminate\Database\Seeder;
use App\VideoWalk;

class VideoWalkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $videos = array([
            'name' => 'Julián',
            'city' => 'Berlín',
            'muxId' => 'Zy7ZTjVoFWVZT00StEDoExDv2pjoBh01enXvnFbnVGDWQ',
            'streamUrl' => 'https://stream.mux.com/y58u8IKFwtNCl3D5h2xALBK5VOzPpyUj582Q95MGXz8.m3u8',
            'videoUrl' => 'https://listeningtothecity.s3.eu-central-1.amazonaws.com/videos/20-Berlin.mov',
            'state' => 3
        ], [
            'name' => 'emilia',
            'city' => 'buenos aires',
            'muxId' => '5WG2odlOzBLd01drZb01LoSS8UoYayTIl5jswUs8gstmo',
            'streamUrl' => 'https://stream.mux.com/35PLSzCsuAlgrRJdSONEN01s6IMtcybbhg5mu268HiZs.m3u8',
            'videoUrl' => 'https://listeningtothecity.s3.eu-central-1.amazonaws.com/videos/9-buenos aires.mp4',
            'state' => 3
        ]);

        foreach ($videos as $video) {
            $video = (object) $video;
            $videoWalk = new VideoWalk();
            $videoWalk->name = $video->name;
            $videoWalk->city = $video->city;
            $videoWalk->muxId = $video->muxId;
            $videoWalk->streamUrl = $video->streamUrl;
            $videoWalk->videoUrl = $video->videoUrl;
            $videoWalk->state = $video->state;
            $videoWalk->save();
        }
    }
}
