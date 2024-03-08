<?php

namespace App\Actions;

use App\Actions\GetImage;
use Illuminate\Bus\Batch;
use Statamic\Contracts\Entries\Entry;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportImage
{
    use AsAction;

    public function handle(Entry $entry): void
    {
        $entry
            ->set('image', GetImage::run($entry))
            ->save();
    }

    public function asJob(?Batch $batch, Entry $entry): void
    {
        if ($batch && $batch->cancelled()) {
            return;
        }

        $this->handle($entry);
    }
}
