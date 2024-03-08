<?php

namespace App\Actions;

use App\Actions\ImportImage;
use Statamic\Actions\Action;

class ImportData extends Action
{
    /**
     * The run method
     *
     * @return void
     */
    public function run($items, $values)
    {
        $items->map(fn ($item) => ImportImage::dispatch($item));

        return __('Importing data ...');
    }
}
