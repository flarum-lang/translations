<?php

namespace App\Commands\Lokalise;

use App\Concerns\GitOutPutHandling;
use App\Extensions\Inventory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Lokalise\LokaliseApiClient;
use Symfony\Component\Finder\Finder;

class ImportTranslations extends Command
{
    use GitOutPutHandling;

    protected $signature = 'lokalise:import {repository}';
    protected $description = 'Imports an existing translation pack into lokalise.';

    public function handle(LokaliseApiClient $lokalise)
    {
        $git = $this->getGit();

        $urlPath = parse_url($repository = $this->argument('repository'), PHP_URL_PATH);
        $urlPath = ltrim($urlPath, '/');
        $name = rtrim($urlPath, '.git');
        $name = str_replace('/', '-', $name);
        $path = storage_path('repositories/' . $name);

        Storage::disk('repositories')->deleteDirectory($name);

        $git->cloneRepository(
            $repository,
            $path
        );

        $locale = $this->identifyLocale($path);

        if (! $locale) {
            $this->error('No locale found.');
            exit;
        }

        $files = $this->files($path);

        if (! $files) {
            $this->error('No files found.');
            exit;
        }

        foreach ($files as $file) {
            $isCore = Str::startsWith($file->getBasename(), 'flarum-')
                || !Str::contains($file->getBasename('.yml'), '-');

            $lokalise->files->upload(
                $isCore ? Inventory::LOKALISE_CORE_PROJECT : Inventory::LOKALISE_OTHER_PROJECT,
                [
                    'data'         => base64_encode($file->getContents()),
                    'filename'     => $file->getBasename(),
                    'lang_iso'     => $locale
                ]
            );
        }
    }

    protected function identifyLocale(string $path): ?string
    {
        $json = file_get_contents("$path/composer.json");
        $composer = json_decode($json, true);

        return Arr::get($composer, 'extra.flarum-locale.code');
    }

    protected function files(string $path)
    {
        $usualLocations = [
            'locale', 'resources/locale', '/'
        ];

        foreach ($usualLocations as $location) {
            if (! is_dir($path . '/' . $location)) continue;

            $finder = (new Finder())
                ->in($path . '/' . $location)
                ->files()
                ->name(['*.yaml', '*.yml']);

            if ($finder->count() > 0) {
                return $finder->getIterator();
            }
        }

        return null;
    }
}
