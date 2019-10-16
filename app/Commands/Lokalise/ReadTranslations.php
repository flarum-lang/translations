<?php

namespace App\Commands\Lokalise;

use App\Extensions\Inventory;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Lokalise\Exceptions\LokaliseResponseException;
use Lokalise\LokaliseApiClient;
use ZipArchive;

class ReadTranslations extends Command
{
    protected $signature = 'lokalise:read';
    protected $description = 'Downloads all translations from Lokalise and stores them locally.';

    public function handle(LokaliseApiClient $lokalise)
    {
        foreach([
            'core' => Inventory::LOKALISE_CORE_PROJECT,
            'extended' => Inventory::LOKALISE_OTHER_PROJECT
                ] as $type => $project) {
            try {
                $response = $lokalise->files->download($project, [
                    'format'             => 'yaml',
                    'original_filenames' => true,
                    'filter_data' => ['reviewed'],
                    'export_empty_as' => 'base'
                ]);
            } catch (LokaliseResponseException $e) {
                $this->error("Unable to download project {$e->getMessage()}");
                continue;
            }

            copy(
                $response->getContent()['bundle_url'],
                $path = storage_path('zips/' . $type)
            );

            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo(base_path('languages/' . $type));
            $zip->close();

            @unlink($path);

            $this->info("Unzipped $type");
        }
    }

    public function schedule(Schedule $schedule)
    {
        $schedule
            ->command(static::class)
            ->hourlyAt(15);
    }
}
