## Bug description

The `AssetContainerContents` are not updated when creating an asset in a job. This results in the job failing. This bug sounds pretty similar to https://github.com/statamic/cms/issues/5501 and should have been closed by https://github.com/statamic/cms/pull/6726.

## Environment

```env
STATAMIC_STACHE_WATCHER=false
QUEUE_CONNECTION=redis
```

Also, make sure to create and migrate a database so that failed jobs can be logged.

## How to reproduce

1. Create a user
3. Run `php artisan queue:work` in your terminal
2. Login to the CP, go to the pages collection, and switch to the list view
4. Open the contextual menu of an entry and run the `Import Data` action

You should now see the `App\Actions\ImportImage` and `Statamic\Listeners\GeneratePresetImageManipulations` jobs fail:

<img width="2056" alt="CleanShot 2024-03-08 at 12 24 59@2x" src="https://github.com/aerni/statamic-assets-cache-bug/assets/23167701/54255e77-df6f-4316-8306-213e613a95aa">
<img width="1557" alt="CleanShot 2024-03-08 at 12 22 17@2x" src="https://github.com/aerni/statamic-assets-cache-bug/assets/23167701/b04d21eb-be72-4f73-a457-09e597bd258b">

## What's going on

These exceptions make sense when you look at the asset container contents. You can confirm that the newly created asset is missing from the list by opening up a Tinkerwell session and getting the contents of the asset container.

<img width="577" alt="CleanShot 2024-03-08 at 12 41 13@2x" src="https://github.com/aerni/statamic-assets-cache-bug/assets/23167701/d4a25901-0e53-421b-a9af-e33676b67730">

The asset container contents are cached in the `all()` method of the `AssetContainerContents` class. When running a queue, this cache is used on subsequent calls to the method. 

Now when an asset is saved in the `save()` method of the `AssetRepository` class, its path is added to the cached container contents. But when the `save()` method on the `AssetContainerContents` is called, the cached instance doesn't include the newly added path.

## How to fix it

There seems to be an easy fix by explicitly adding the new path to the cache:

```diff
public function add($path)
{
    if (! $metadata = $this->getNormalizedFlysystemMetadata($path)) {
        return $this;
    }

    // Add parent directories
    if (($dir = dirname($path)) !== '.') {
        $this->add($dir);
    }

-    $this->all()->put($path, $metadata);
+    $files = $this->all()->put($path, $metadata);
+
+    if (Statamic::isWorker()) {
+        Cache::put($this->key(), $files, $this->ttl());
+    }

    $this->filteredFiles = null;
    $this->filteredDirectories = null;

    return $this;
}
