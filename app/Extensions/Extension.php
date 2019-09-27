<?php

namespace App\Extensions;

use Illuminate\Support\Fluent;

/**
 * @property string $name
 * @property string $file
 * @property string $repository
 * @property string $directory
 * @property string $matches
 * @property string $lang
 * @property string $lokalise_project
 */
class Extension extends Fluent
{
    public function baseName(): string
    {
        return basename($this->file, '.yml');
    }
    public function zipName(): string
    {
        return sprintf('%s.zip', $this->baseName());
    }

    public function isCore(): bool
    {
        return $this->baseName() === 'core';
    }
    public function project(): string
    {
        return $this->isCore() ? Inventory::LOKALISE_CORE_PROJECT : Inventory::LOKALISE_OTHER_PROJECT;
    }
}
