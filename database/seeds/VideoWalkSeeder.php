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
        $videoWalk = new VideoWalk();
        $videoWalk->videoUrl = 'https://listeningtothecity.s3.eu-central-1.amazonaws.com/videos/20-Berlin.mov';
        $videoWalk->state = 3;
        $videoWalk->name = 'Julián';
        $videoWalk->city = 'Berlín';
        $videoWalk->streamUrl = 'https://stream.mux.com/y58u8IKFwtNCl3D5h2xALBK5VOzPpyUj582Q95MGXz8.m3u8';
        $videoWalk->muxId = 'Zy7ZTjVoFWVZT00StEDoExDv2pjoBh01enXvnFbnVGDWQ';
        $videoWalk->save();
    }
}
