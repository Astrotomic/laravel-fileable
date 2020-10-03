<?php

namespace Astrotomic\Fileable\Contracts;

use Astrotomic\Fileable\FileAdder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Fileable
{
    public function files(): MorphMany;

    public function addFile($file): FileAdder;
}
