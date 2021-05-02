<?php

namespace Astrotomic\Fileable\Tests\Models;

use Astrotomic\Fileable\Concerns\HasFiles;
use Astrotomic\Fileable\Contracts\Fileable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Fileable
{
    use HasFiles;
}
