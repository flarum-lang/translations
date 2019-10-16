<?php

namespace App\Commands;

use App\Concerns\GitOutPutHandling;
use App\Translation;
use App\Translations;
use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Subsplit extends Command
{
    use GitOutPutHandling;

    protected $signature = 'subsplit';
    protected $description = 'Pushes changes of languages onto their own repositories.';

    public function handle(Translations $translations)
    {
        $git = $this->getGit();

        $this->deleteSubsplitDirectory();

//        $this->preventChanges($git);

        $this->init($git);

        $this->split($git, $translations);

        $this->deleteSubsplitDirectory();
    }

    protected function init(GitWrapper $git)
    {
        $remote = new GitCommand('remote', 'get-url', '--push', 'origin');
        $remote = trim($git->run($remote, base_path()));

        $init = new GitCommand('subsplit', 'init', $remote);
        $git->run($init, base_path());
    }

    protected function split(GitWrapper $git, Translations $translations)
    {
        $translations->each(function (Translation $translation) use ($git) {
            if (is_dir($translation->corePath())) {
                $split = new GitCommand(
                    'subsplit',
                    'publish',
                    '--heads=master',
                    $this->useToken($translation->coreSubsplit())
                );

                $git->run($split, base_path());
            } else {
                $this->warn('Core path does not exist: ' . $translation->corePath());
            }

            if (is_dir($translation->extendedPath())) {
                $split = new GitCommand(
                    'subsplit',
                    'publish',
                    '--heads=master',
                    $this->useToken($translation->extendedSubsplit())
                );

                $git->run($split);

                $this->info("Subsplit of {$translation->extendedSubsplit()}.");
            } else {
                $this->warn('Extended path does not exist: ' . $translation->extendedPath());
            }
        });
    }

    protected function preventChanges(GitWrapper $git)
    {
        $dir = $git->workingCopy(base_path());

        if ($dir->hasChanges() || !$dir->isUpToDate()) {
            $this->error('Make sure all changes are committed and your local version is up to date.');

            exit;
        }
    }

    protected function deleteSubsplitDirectory(): void
    {
        Storage::disk('root')->deleteDirectory('.subsplit');
    }

    protected function useToken(string $command): string
    {
        if ($token = getenv('GITHUB_TOKEN') && $actor = getenv('GITHUB_ACTOR')) {
            return str_replace('git@github.com:', "https://$actor:$token@github.com/", $command);
        }

        return $command;
    }
}
