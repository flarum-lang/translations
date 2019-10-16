<?php

namespace App\Commands\Lokalise;

use App\Concerns\GitOutPutHandling;
use App\Extensions\Extension;
use App\Extensions\Inventory;
use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Lokalise\LokaliseApiClient;
use PharIo\Version\InvalidVersionException;
use PharIo\Version\Version;
use Symfony\Component\Finder\Finder;

class PushTranslations extends Command
{
    use GitOutPutHandling;

    protected $signature = 'lokalise:push';
    protected $description = 'Downloads configured extensions and pushes all translations to Lokalise.';

    public function handle(Inventory $inventory, LokaliseApiClient $lokalise)
    {
        $git = $this->getGit();

        $inventory
            ->each(function (Extension $extension) use ($git, $lokalise) {
                Storage::disk('repositories')->deleteDirectory($extension->baseName());

                $tag = $this->latestTag($extension) ?? 'dev-master';

                $workingCopy = $git->cloneRepository(
                    $extension->repository,
                    $path = storage_path('repositories/' . $extension->baseName()),
                    ['branch' => $tag, '--config' => 'advice.detachedHead=false']
                );

                $this->info("Cloned repository {$extension->repository} for version {$tag} to {$workingCopy->getDirectory()}");

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

                    $this->info("Uploaded {$extension->baseName()}");
                }
            });
    }

    protected function latestTag(Extension $extension): ?string
    {
        $output = (new GitWrapper())->run(new GitCommand('ls-remote', '--tags', $extension->repository));

        $highest = null;


        foreach (explode("\n", $output) as $line) {
            if (empty($line)) continue;

            list($_, $version) = explode("\t", $line);
            $version = Str::after($version, 'refs/tags/');

            try {
                $version = new Version($version);
            } catch(InvalidVersionException $e) {
                continue;
            }

            if ($highest === null || ($highest && $version->isGreaterThan($highest))) {
                $highest = $version;
            }
        }

        return $highest instanceof Version ? $highest->getVersionString() : null;
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
