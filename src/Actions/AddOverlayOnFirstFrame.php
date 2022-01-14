<?php

namespace ExactSports\MediaManipulation\Actions;

use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;

class AddOverlayOnFirstFrame
{
    public function execute(string $highlight_id, int $x, int $y, string $text, int $timestamp)
    {
        // Check if the first frame of the highlight has already created
        $first_frame_image_path = 'firstframes/' . $highlight_id . '_firstframe_image_'.$timestamp.'.jpg';
        if (!Storage::exists($first_frame_image_path)) {
            $response = array(
                'status' => 0,
                'image_path' => 'NA',
                'message' => "Not found. The image of the first frame has not yet created."
            );
            return response()->json($response);
        }

        // Merge shape and first frame
        $frame = Image::make(Storage::get($first_frame_image_path));

        // default params
        $height      = 1080;
        $max_len     = 26;
        $max_width   = 265;
        $font_height = 54;
        $textBoxPadding = 12;
        $spinningSize = 350;

        $lines = explode("\n", wordwrap($text, $max_len));

        foreach($lines as $index => $line)
        {
            $line_height = $height - $textBoxPadding - ((count($lines) - $index - 1) * $font_height);
            // Insert Text
            $frame->text($line, $max_width, $line_height, function ($font) {
                $font->file(public_path('fonts/BebasNeue-Regular.ttf'));
                $font->size(54);
                $font->color('#fff');
                $font->align('center');
                $font->valign('bottom');
                $font->angle(0);
            });
        }

        // Insert Rectangle
        $rectStartPointX = 5;
        $rectStartPointY = $height - ($textBoxPadding + count($lines) * $font_height);
        $rectEndPointX = $rectStartPointX + $max_width * 2;
        $rectEndPointY = $height - ($textBoxPadding / 2);
        $frame->rectangle($rectStartPointX, $rectStartPointY, $rectEndPointX, $rectEndPointY, function ($draw) {
            $draw->background('rgba(255, 255, 255, 0)');
            $draw->border(3, '#fff');
        });

        // Insert Line
        $frame->line($rectEndPointX / 2, $rectStartPointY, $x, $y + ($spinningSize / 2), function ($draw) {
            $draw->color('#fff');
        });

        // finally we save the image as a new file
        $frame->save($highlight_id.'-'.$timestamp.'.jpg');

        for($i = 0; $i <=36; $i++){
            shell_exec('ffmpeg -i '.$highlight_id.'-'.$timestamp.'.jpg  -i highlight-spinning-circle.png -filter_complex "[1:v] rotate='.$i.'*10*PI/180:c=0x00000000:ow=rotw(iw):oh=roth(ih),scale2ref=w='.$spinningSize.':h='.$spinningSize.' [wm][vid];[vid][wm] overlay='.$x.':'.$y.'" -codec:a copy '.$highlight_id.'-'.$timestamp.'-'.$i.'.jpg');
        }

        // generate the overlay video clip
        shell_exec("ffmpeg -r 60 -f image2 -s 1920x1080 -i ".$highlight_id."-".$timestamp."-%d.jpg -vcodec libx264 -crf 25  -pix_fmt yuv420p ".$highlight_id."_match_".$timestamp.".mp4");

        // remove temp images
        $frameImagePath = $highlight_id.'-'.$timestamp.'.jpg';
        if(File::exists($frameImagePath)){
            File::delete($frameImagePath);
        }
        for($i=0; $i<=36;$i++){
            $image_path = $highlight_id."-".$timestamp."-".$i.".jpg";  // Value is not URL but directory file path
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
        }

        return response()->json(["shape_type" => Storage::url($first_frame_image_path)]);
    }
}
