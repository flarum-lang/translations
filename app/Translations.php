<?php

namespace App;

use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

/**
 * @mixin Collection
 */
class Translations
{
    /**
     * @var static
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = collect(Yaml::parseFile(base_path('translations.yml')))
            ->map(function ($translation) {
                return new Translation($translation);
            })
            ->keyBy('code');
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->translations, $name], $arguments);
    }
}
