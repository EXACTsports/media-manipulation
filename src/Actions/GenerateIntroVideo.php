<?php

namespace ExactSports\MediaManipulation\Actions;

use Exception;

class GenerateIntroVideo
{
    public function execute(string $highlight_id, array $data)
    {
        // basic data
        $athlete_photo = "removed-background".$highlight_id.'.png';
        $background = "camper_intro_background.mp4";
        $athlete_name = $data["name"];
        
        try {
            // add Athlete's name
            shell_exec('ffmpeg -i '.$background.' -vf drawtext="fontfile=path: fontsize=48:fontcolor=red:x=10:y=10:text=\''.$athlete_name.'\'" output_added_by_name.mp4');

            // image resize
            shell_exec('ffmpeg -i '.$athlete_photo.' -vf scale=180:240 '.$athlete_photo);

            // add Athlete's photo
            shell_exec('ffmpeg -i output_added_by_name.mp4 -i '.$athlete_photo.' -filter_complex "[v0][v1]blend=all_mode=overlay:all_opacity=0.7,format=yuv420p[v]" output_added_by_photo.mp4');

            // animate the athlete's name
            $spilt_athlete_name = explode(" ", $athlete_name);
            shell_exec('ffmpeg -i 1.mp4 -vf "[in]drawtext=fontfile=./font.ttf:text=\''.$spilt_athlete_name[0].'\':fontcolor=white:fontsize=150:x=(w-text_w)/2:y=if(lt(t,1),(20h-((3h-100)t/1.2)),(h+100)/2):enable=\'between(t,0.5,3)\',drawtext=fontfile=./font.ttf:text=\''.$spilt_athlete_name[1].'\':fontcolor=white:fontsize=150:x=(w-text_w)/2:y=if(lt(t,1),(20h-((3*h-400)*t/1.2)),(h+400)/2):enable=\'between(t,1,3)\',drawtext=fontfile=./font.ttf:text=\''.$spilt_athlete_name[2].'\':fontcolor=#6D3100:fontsize=150:x=(w-text_w)/2:y=(h+100)/2:enable=\'between(t,2.5,5)\',drawtext=fontfile=./font.ttf:text=\''.$athlete_name.'\':fontcolor=#6D3100:fontsize=150:x=(w-text_w)/2:y=(h+400)/2:enable=\'between(t,2.5,5)\'[out]" '.$highlight_id.'_intro.mp4');
            
            // Apply interval into an overlay text
            shell_exec('ffmpeg -i output_added_by_photo.mp4 -vf "drawtext=fontsize=40:fontcolor=yellow:x=(w-text_w)/2:y=h-60*t:text=\''.$data["organizer"].'\':enable=\'between(t,2,3)\'" -c:v libx264 -t 10 '.$highlight_id.'_intro.mp4');
            shell_exec('ffmpeg -i output_added_by_photo.mp4 -vf "drawtext=fontsize=40:fontcolor=yellow:x=(w-text_w)/2:y=h-60*t:text=\''.$data["position"].'\':enable=\'between(t,2,3)\'" -c:v libx264 -t 10 '.$highlight_id.'_intro.mp4');
            shell_exec('ffmpeg -i output_added_by_photo.mp4 -vf "drawtext=fontsize=40:fontcolor=yellow:x=(w-text_w)/2:y=h-60*t:text=\''.$data["address"].'\':enable=\'between(t,2,3)\'" -c:v libx264 -t 10 '.$highlight_id.'_intro.mp4');
            shell_exec('ffmpeg -i output_added_by_photo.mp4 -vf "drawtext=fontsize=40:fontcolor=yellow:x=(w-text_w)/2:y=h-60*t:text=\''.$data["phone"].'\':enable=\'between(t,2,3)\'" -c:v libx264 -t 10 '.$highlight_id.'_intro.mp4');

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
