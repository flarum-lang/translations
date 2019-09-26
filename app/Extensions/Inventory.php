<?php

namespace App\Extensions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

/**
 * @mixin Collection
 */
class Inventory
{
    /** @var Collection|Extension[] */
    protected $extensions;

    const LOKALISE_CORE_PROJECT = '722660935d41917e602af4.06892971';
    const LOKALISE_OTHER_PROJECT = '905741775d41e764bd0b00.56796766';

    public function __construct()
    {
        $this->extensions = collect(Storage::files())
            ->map(function ($file) {
                $extension = new Extension(Yaml::parse(Storage::get($file)));
                $extension->file = $file;

                return $extension;
            });
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->extensions, $name], $arguments);
    }
}
