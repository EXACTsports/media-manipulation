<?php

namespace ExactSports\MediaManipulation\Actions;

use Exception;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class SpiltVideoWithTimestamps
{
    public function execute(string $highlight_id, string $source_video, array $timestamps)
    {
        // sort timestamps by ASC
        sort($timestamps);

        // spilt video by timestamps
        try {
            $start = 0;
            foreach($timestamps as $to){
                $fileName = $highlight_id . "_match_" . $start . "_" . $to . ".mp4";
                FFMpeg::open($source_video)
                    ->export()
                    ->addFilter(['-ss', $start])
                    ->addFilter(['-t', $to - $start])
                    ->save($fileName);
                $start = $to;
            }

            // last video clip
            $fileName = $highlight_id . "_match_" . $start . "_.mp4";
            FFMpeg::open($source_video)
                ->export()
                ->addFilter(['-ss', $start])
                ->save($fileName);

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
