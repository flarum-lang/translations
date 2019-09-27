<?php

namespace App\Concerns;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;
use GitWrapper\GitWrapper;
use LaravelZero\Framework\Commands\Command;

trait GitOutPutHandling
{
    protected function getGit(): GitWrapper
    {
        $git = new GitWrapper();

        $git->addOutputListener(new class($this) implements GitOutputListenerInterface {
            /**
             * @var Command
             */
            private $command;

            public function __construct(Command $command)
            {
                $this->command = $command;
            }

            public function handleOutput(GitOutputEvent $gitOutputEvent): void
            {
                $this->command->comment($gitOutputEvent->getBuffer());
            }
        });

        return $git;
    }
}
