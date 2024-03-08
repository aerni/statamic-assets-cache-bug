<?php

namespace App\Actions;

use App\Actions\CreateAssetFromUrl;
use Lorisleiva\Actions\Concerns\AsAction;
use Statamic\Contracts\Entries\Entry;

class GetImage
{
    use AsAction;

    public function handle(Entry $entry): string
    {
        $assetId = CreateAssetFromUrl::run(
            url: 'https://statamic.com/img/branding/buckshot-thunderstride.jpg',
            basename: 'buckshot-thunderstride.jpg',
            field: $entry->blueprint()->field('image')
        );

        return $entry->blueprint()
            ->field('image')
            ->setValue($assetId)
            ->process()
            ->value();
    }
}
