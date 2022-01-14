<?php

namespace ExactSports\MediaManipulation\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class GetCamperFromApi
{
    public function __construct(public string $uuid)
    {
    }

    public function execute(): array
    {
        if (config('app.env') === 'local') {
            if (file_exists(storage_path('campers/' . $this->uuid))) {
                return json_decode(File::get(storage_path('campers/' . $this->uuid)), true);
            }
        }
        return Cache::remember('camper-' . $this->uuid, config('env.camper_cache_ttl', 1), function () {
            $camper = json_decode(Http::get(config('env.api_location') . '/api/clients/camper_dashboard/' . $this->uuid)->body(), true)['data'] ?? [];
            /*
            if (config('app.env') !== 'production') {
                File::put(storage_path('campers/' . $this->uuid), json_encode($camper));
            }
            */
            return $camper;
        });
    }

    public function fresh(): self
    {
        Cache::forget('camper-' . $this->uuid);
        return $this;
    }
}
