<?php

namespace App\Commands;

use App\Translation;
use App\Translations;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Stubs extends Command
{
    protected $signature = 'stubs';
    protected $description = 'Adds and updates readme, composer and extend.php on language directories.';

    public function handle(Translations $translations)
    {
        $stubs = Storage::disk('stubs')->files();

        $translations->each(function (Translation $translation) use ($stubs) {
            $disk = Storage::disk('languages');
            foreach (['core', 'extended'] as $type) {
                if ($disk->exists($path = $type . '/' . $translation->code)) {
                    foreach ($stubs as $file) {
                        $disk->put(
                            $path . '/' . $file,
                            $this->format(Storage::disk('stubs')->get($file), $translation, $type)
                        );
                    }
                }
            }
        });
    }

    protected function format(string $contents, Translation $translation, $type): string
    {
        $contents = str_replace(':repository:', $translation->repository[$type], $contents);
        $contents = str_replace(':packagist:', $translation->packagist($type), $contents);

        foreach(array_keys($translation->getAttributes()) as $key) {
            if (is_array($value = $translation->$key)) continue;
            $contents = str_replace(":$key:", $value, $contents);
        }

        return $contents;
    }
}
