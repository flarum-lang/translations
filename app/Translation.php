<?php

namespace App;

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

    public function extendedPath()
    {
        return base_path('languages/other/' . $this->code);
    }

    public function extendedSubsplit()
    {
        return sprintf(
            '%s:%s',
            'languages/other/' . $this->code,
            $this->repository['extended']
        );
    }
}
