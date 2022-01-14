<?php

namespace ExactSports\MediaManipulation\Actions;

use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class MergeFinalVideoWithHighlightId
{
    public function execute(string $highlight_id)
    {
        // generate video file names
        $fileNames = [
            $highlight_id.'_match.mp4',
            $highlight_id.'_intro.mp4',
            'shapes/intro_logo.mp4'
        ];

        // merge video by hightlight_id
        try {
            FFMpeg::open($fileNames)
                ->export()
                ->inFormat(new X264)
                ->concatWithTranscoding($hasVideo = true, $hasAudio = false)
                ->save($highlight_id.'_final.mp4');
        } catch (Exception $exception) {
            $response = array(
                'status' => 0,
                'message' => $exception->getMessage()
            );
            return response()->json($response);
        }
        
        $response = array(
            'status' => 1,
            'message' => "Action done successfully."
        );
        return response()->json($response);
    }
}
