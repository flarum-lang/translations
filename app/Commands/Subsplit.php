<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class Subsplit extends Command
{
    protected $signature = 'subsplit';
    protected $description = 'Pushes changes of languages onto their own repositories.';

}
