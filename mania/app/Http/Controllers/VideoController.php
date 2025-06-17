<?php
namespace App\Http\Controllers;

use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        return Video::select('id', 'youtube_id', 'title')->get();
    }
}
