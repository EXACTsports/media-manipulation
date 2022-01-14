<?php

namespace ExactSports\MediaManipulation\Actions;

use Exception;
use FFMpeg\Filters\Frame\FrameFilters;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ExtractFirstFrameOfHighlightFromVideo
{
    public function execute(string $highlight_id, string $source_video, string $start)
    {
        // Get get first frame from source video
        $generated_image_name = $highlight_id . '_firstframe_image_' . $start . '.jpg';
        $generated_image_path = "firstframes/" . $generated_image_name;

        // eg: app/video/sample.mp4
        try {
            FFMpeg::open($source_video)
                ->getFrameFromSeconds($start)
                ->export()
                ->addFilter(function (FrameFilters $filters) {
                    $filters->custom('scale=1920:1080');
                    //$filters->custom('scale=1280:528');
                })
                ->save($generated_image_path);
        } catch (Exception $exception) {
            $response = array(
                'status' => 0,
                'image_path' => 'NA',
                'message' => $exception->getMessage()
            );
            return response()->json($response);
        }

        $response = array(
            'status' => 1,
            'image_path' => $generated_image_path,
            'message' => "Image has been created successfully."
        );
        return response()->json($response);
    }
}
