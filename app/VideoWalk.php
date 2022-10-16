<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VideoWalk extends Model
{
    public function getSubtitlesAttribute() {
        $subsPath = '/subs/' . $this->id . '.vtt';

        return Storage::disk('public')->exists($subsPath) ? Storage::disk('public')->url($subsPath) : '';
    }

    public function getPosterAttribute() {
        $thumbPath = '/thumbs/' . $this->id . '.jpg';
        return Storage::disk('public')->exists($thumbPath) ? Storage::disk('public')->url($thumbPath) : '';
    }
}
