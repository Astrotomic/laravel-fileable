<?php

namespace Astrotomic\Fileable\Concerns;

use Astrotomic\Fileable\Contracts\File as FileContract;
use Astrotomic\Fileable\FileAdder;
use Astrotomic\Fileable\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @property-read Collection|File[] $files
 * @property-read int|null $files_count
 *
 * @mixin Model
 */
trait Fileable
{
    public static function bootFileable(): void
    {
        static::deleting(static function (self $model): ?bool {
            if (array_key_exists(SoftDeletes::class, class_uses_recursive($model))) {
                /** @var Model|SoftDeletes $model */
                if (! $model->isForceDeleting()) {
                    return null;
                }
            }

            return $model->files()->cursor()->every(fn (Model $file): bool => $file->delete()) === true ? null : false;
        });
    }

    /**
     * @return MorphMany|File|FileContract
     */
    public function files(): MorphMany
    {
        return $this->morphMany(config('fileable.file_model', File::class), 'fileable');
    }

    /**
     * @param string|UploadedFile|SymfonyFile|resource $file
     * @return FileAdder
     */
    public function addFile($file): FileAdder
    {
        return app(FileAdder::class)
            ->add($file)
            ->to($this);
    }

    public function addFileFromRequest(string $key): FileAdder
    {
        return $this->addFile(request()->file($key));
    }

    public function addFileFromDisk(string $path, ?string $disk = null): FileAdder
    {
        return $this->addFile(Storage::disk($disk)->readStream($path))
            ->as(basename($path));
    }

    public function addFileFromUrl(string $url): FileAdder
    {
        return $this->addFile(fopen($url, 'r'))
            ->as(urldecode(basename(parse_url($url, PHP_URL_PATH))));
    }
}
