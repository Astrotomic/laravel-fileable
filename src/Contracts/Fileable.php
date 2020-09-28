<?php

namespace Astrotomic\Fileable\Contracts;

use Astrotomic\Fileable\FileAdder;

interface Fileable
{
    public function addFile($file): FileAdder;
}
