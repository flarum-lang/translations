<?php

namespace App\Commands\Lokalise;

use App\Extensions\Extension;
use App\Extensions\Inventory;
use GitWrapper\GitWrapper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Lokalise\Exceptions\LokaliseResponseException;
use Lokalise\LokaliseApiClient;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class PushTranslations extends Command
{
    protected $signature = 'lokalise:push';
    protected $description = 'Downloads configured extensions and pushes all translations to Lokalise.';

    public function handle(Inventory $inventory, LokaliseApiClient $lokalise, GitWrapper $git)
    {
        $inventory
            ->each(function (Extension $extension) use ($git, $lokalise) {
                Storage::disk('repositories')->deleteDirectory($extension->baseName());
                $git->cloneRepository($extension->repository, $path = storage_path('repositories/' . $extension->baseName()));

                $concat = '';

                foreach ((new Finder())
                    ->files()
                    ->in($path . '/' . $extension->directory)
                    ->name($extension->matches) as $file) {
                    if ($extension->isCore()) {
                        $this->upload($lokalise, $extension, $file->getFilename(), $file->getContents());
                    } else {
                        $concat .= $file->getContents();
                    }
                }

                if (! empty($concat)) {
                    $this->upload($lokalise, $extension, $extension->baseName() . '.yml', $concat);
                }

                Storage::disk('repositories')->deleteDirectory($extension->baseName());
            });
    }

    protected function upload(LokaliseApiClient $lokalise, Extension $extension, string $name, string $contents)
    {
        $lokalise->files->upload($extension->project(), [
            'data'         => base64_encode($contents),
            'filename'     => $name,
            'lang_iso'     => $extension->lang,
            'cleanup_mode' => true
        ]);
    }

    public function schedule(Schedule $schedule)
    {
        $schedule
            ->command(static::class)
            ->hourlyAt(5);
    }
}
