<?php

namespace Astrotomic\Fileable\Contracts;

use Closure;

/**
 * @property string|null $disk
 * @property string|null $mimetype
 * @property int|null $size
 * @property string|null $display_name
 * @property string|null $filepath
 * @property string|null $filename
 * @property array|null $meta
 */
interface File
{
    public function exists(): bool;

    /**
     * @return resource|null
     */
    public function stream();

    /**
     * @param string|resource $contents
     * @param array $options
     *
     * @return bool
     */
    public function store($contents, array $options = []): bool;

    public static function storing(Closure $callback): void;

    public static function stored(Closure $callback): void;
}
