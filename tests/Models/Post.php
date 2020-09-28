<?php

namespace Astrotomic\Fileable\Tests\Models;

use Astrotomic\Fileable\Concerns\Fileable;
use Astrotomic\Fileable\Contracts\Fileable as FileableContract;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements FileableContract
{
    use Fileable;
}
