<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Statamic\Fields\Field;
use Statamic\Forms\Uploaders\AssetsUploader;

class CreateAssetFromUrl
{
    use AsAction;

    public function handle(string $url, string $basename, Field $field): string
    {
        $temporaryDirectory = TemporaryDirectory::make();
        $temporaryFilePath = $temporaryDirectory->path($basename);

        File::put($temporaryFilePath, Http::get($url)->body());

        $uploadedFile = new UploadedFile($temporaryFilePath, $basename);

        $assetId = AssetsUploader::field($field)->upload($uploadedFile);

        $temporaryDirectory->delete();

        return $assetId;
    }
}
