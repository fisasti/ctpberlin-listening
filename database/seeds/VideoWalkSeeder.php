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
        $videoWalk->state = 1;
        $videoWalk->name = 'Julián';
        $videoWalk->city = 'Berlín';
        $videoWalk->save();
    }
}
