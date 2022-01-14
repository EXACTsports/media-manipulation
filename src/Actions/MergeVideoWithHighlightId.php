<?php

namespace ExactSports\MediaManipulation\Actions;

use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class MergeVideoWithHighlightId
{
    public function execute(string $highlight_id, array $timestamps)
    {
        // sort timestamps by ASC
        sort($timestamps);

        // generate video file names from timestamp
        $fileNames = [];
        $start = 0;
        foreach($timestamps as $index => $timestamp){
            if($timestamps == 0){
                array_push($fileNames, $highlight_id.'/'.$highlight_id.'_match_0.mp4');
            }else{
                array_push($fileNames, $highlight_id.'/'.$highlight_id.'_match_'.$start.'_'.$timestamp.'.mp4');
                array_push($fileNames, $highlight_id.'/'.$highlight_id.'_match_'.$timestamp.'.mp4');
            }
            if($index == sizeof($timestamps) - 1) {
                array_push($fileNames, $highlight_id.'/'.$highlight_id.'_match_'.$timestamp.'_.mp4');
            }
        }

        // merge video by hightlight_id
        try {
            FFMpeg::open($fileNames)
                ->export()
                ->inFormat(new X264)
                ->concatWithTranscoding($hasVideo = true, $hasAudio = false)
                ->save($highlight_id.'_match.mp4');
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
