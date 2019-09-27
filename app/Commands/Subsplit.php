<?php

namespace App\Commands;

use App\Translation;
use App\Translations;
use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use LaravelZero\Framework\Commands\Command;

class Subsplit extends Command
{
    protected $signature = 'subsplit';
    protected $description = 'Pushes changes of languages onto their own repositories.';

    public function handle(GitWrapper $git, Translations $translations)
    {
        $this->preventChanges($git);

        $this->init($git);

        $this->split($git, $translations);
    }

    protected function init(GitWrapper $git)
    {
        $remote = new GitCommand('remote', 'get-url', '--push', 'origin');
        $remote = $git->run($remote);

        $init = new GitCommand('subsplit', 'init', $remote);
        $git->run($init);
    }

    protected function split(GitWrapper $git, Translations $translations)
    {
        $translations->each(function (Translation $translation) use ($git) {
            $split = new GitCommand(
                'subsplit',
                'publish',
                '--heads="master"',
                $translation->coreSubsplit()
            );

            $git->run($split);

            $split = new GitCommand(
                'subsplit',
                'publish',
                '--heads="master"',
                $translation->extendedSubsplit()
            );

            $git->run($split);
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
}
