<?php

namespace App;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Fluent;

/**
 * @property string $code
 * @property string $title
 * @property array|string $repository
 */
class Translation extends Fluent
{
    public function corePath()
    {
        return base_path('languages/core/' . $this->code);
    }

    public function coreSubsplit()
    {
        return sprintf(
            '%s:%s',
            'languages/core/' . $this->code,
            $this->repository['core']
        );
    }

    public function packagist(string $type)
    {
        preg_match('/(?<name>[^\/:]+\/[^\/\.]+)(\.git)?$/', $this->repository[$type], $m);
      
        return $m['name'];
    }

    public function extendedPath()
    {
        return base_path('languages/extended/' . $this->code);
    }

    public function extendedSubsplit()
    {
        return sprintf(
            '%s:%s',
            'languages/extended/' . $this->code,
            $this->repository['extended']
        );
    }
}
