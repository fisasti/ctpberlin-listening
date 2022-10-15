<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VideoWalk extends Model
{
    public function getSubtitlesAttribute() {
        $subsPath = 'subs/' . $this->id . '.vtt';
        return Storage::exists($subsPath) ? $subsPath : '';
    }
}
