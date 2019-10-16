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
            private $masked = ['GITHUB_TOKEN', 'GITHUB_PERSONAL_ACCESS_TOKEN'];

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
                $buffer = str_replace(array_walk($this->masked, function ($env) {
                    return getenv($env);
                }), '*** masked ***', $gitOutputEvent->getBuffer());

                $this->command->comment($buffer);
            }
        });

        return $git;
    }
}
