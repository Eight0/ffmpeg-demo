<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FFMpeg;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->divide(3, 2, (storage_path().'/demo.mp4'));
        dd($data);
//// This is in the main doc...
//        $ffprobe
//                ->streams($full_video_path) // extracts streams informations
//                ->videos()                      // filters video streams
//                ->first()                       // returns the first video stream
//                ->get('codec_name');
        $ffmpeg = FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg', // the path to the FFMpeg binary
                'ffprobe.binaries' => '/usr/bin/ffprobe', // the path to the FFProbe binary
                'timeout'          => 3600, // the timeout for the underlying process
                'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
        ]);
        $video = $ffmpeg->open(storage_path() . '/demo.mp4');
//        dd($video);
        $video->filters()
                ->resize(new FFMpeg\Coordinate\Dimension(320, 240))
                ->synchronize();
//        $video
//                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
//                ->save('frame.jpg');
//        $video
//                ->save(new FFMpeg\Format\Video\X264('libmp3lame', 'libx264'), storage_path() . '/export-x264.mp4');
//                ->save(new FFMpeg\Format\Video\WMV(), storage_path() . '/export-wmv.wmv')
//                ->save(new FFMpeg\Format\Video\WebM(), storage_path() . '/export-webm.webm');
        $video->filters()->crop(new FFMpeg\Coordinate\Point(640, 273, true), new FFMpeg\Coordinate\Dimension(640, 273));
//        $video->filters()->crop(new FFMpeg\Coordinate\Point("t*100", 0, true), new FFMpeg\Coordinate\Dimension(200, 600))->synchronize();
        $video->save(new FFMpeg\Format\Video\X264('libmp3lame', 'libx264'), storage_path() . '/5-5.mp4');
        return response()->json(['data' => 'success']);
    }


    public function divide($x, $y, $path)
    {
        $ffprobe = FFMpeg\FFProbe::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
        ]);
        $video_dimensions = $ffprobe
                ->streams($path)
                ->videos()
                ->first()
                ->getDimensions();
        $width = $video_dimensions->getWidth();
        $height = $video_dimensions->getHeight();

        $one_width = $width/$x;
        $one_height = $height/$y;

        $ffmpeg = FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg', // the path to the FFMpeg binary
                'ffprobe.binaries' => '/usr/bin/ffprobe', // the path to the FFProbe binary
                'timeout'          => 3600, // the timeout for the underlying process
                'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
        ]);
        $video = $ffmpeg->open(storage_path() . '/demo.mp4');

//        ffmpeg -i demo.mp4 -vf crop=200:200 33.mp4
//        ffmpeg -i demo.mp4 -vf crop=640:273 -threads 5 -preset ultrafast -strict -2 1-1.mp4

        $data = [];
        if ((int) $x * (int) $y > 1) {
            for ($j = 1; $j <= $y; $j++) {
                for ($i = 1; $i <= $x; $i++) {
                    $video->filters()->crop(new FFMpeg\Coordinate\Point(($i-1)*$one_width, ($j-1)*$one_height, false), new FFMpeg\Coordinate\Dimension($one_width, $one_height));
                    $video->save(new FFMpeg\Format\Video\X264('libmp3lame', 'libx264'), storage_path() . '/' .$i . '-' .$j. '.mp4');
                }
            }
        }

        return $data;



    }
}
