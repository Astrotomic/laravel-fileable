<?php

namespace Astrotomic\Fileable\Concerns;

use Astrotomic\Fileable\FileAdder;
use Astrotomic\Fileable\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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

            return $model->files()->cursor()->every(fn (File $file): bool => $file->delete());
        });
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @param string|UploadedFile|SymfonyFile $file
     * @return FileAdder
     */
    public function addFile($file): FileAdder
    {
        return app(FileAdder::class)
            ->add($file)
            ->to($this);
    }
}
