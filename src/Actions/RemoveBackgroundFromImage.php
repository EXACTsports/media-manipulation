<?php

namespace ExactSports\MediaManipulation\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client as Client;

class RemoveBackgroundFromImage
{
    public function execute(string $imageUrl): string
    {
        // Create a UUID to use as the filename of the transformed image
        $key = Str::orderedUuid();

        // Get image file from image path
        $image = fopen($imageUrl, 'r');

        // Send image to API This results in a 404 at the moment.
        $client = new Client();
        $response = $client->post('https://api.remove.bg/v1.0/removebg', [
            'multipart' => [
                [
                    'name'     => 'image_file',
                    'contents' => $image
                ],
                [
                    'name'     => 'size',
                    'contents' => 'auto'
                ]
            ],
            'headers' => [
                'X-Api-Key' => config('services.background_remover.key')
            ]
        ]);

        // Get the resulting image back from the API. This is written as if the API call returns the transformed image
        // in its binary form, which it doesn't. So this will have to be changed to store the actual file.
        Storage::put("removed-background/{$key}.png", $response->getBody());

        return $key; // Return the UUID so that the calling function can store / use it.

    }
}
