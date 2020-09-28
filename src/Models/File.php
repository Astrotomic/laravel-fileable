<?php

namespace Astrotomic\Fileable\Models;

use Astrotomic\Fileable\Concerns\Fileable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property int $id
 * @property string $fileable_type
 * @property int $fileable_id
 * @property string|null $disk
 * @property string|null $mimetype
 * @property int|null $size
 * @property string|null $name
 * @property string|null $filepath
 * @property string|null $filename
 * @property string|null $url
 * @property string|null $extension
 * @property string|null $basename
 * @property array|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $modified_at
 * @property-read Model|Fileable $fileable
 *
 * @method static Builder|File newModelQuery()
 * @method static Builder|File newQuery()
 * @method static Builder|File query()
 * @method static Builder|File whereCreatedAt($value)
 * @method static Builder|File whereDisk($value)
 * @method static Builder|File whereFileableId($value)
 * @method static Builder|File whereFileableType($value)
 * @method static Builder|File whereFilename($value)
 * @method static Builder|File whereFilepath($value)
 * @method static Builder|File whereId($value)
 * @method static Builder|File whereMeta($value)
 * @method static Builder|File whereMimetype($value)
 * @method static Builder|File whereName($value)
 * @method static Builder|File whereSize($value)
 * @method static Builder|File whereUpdatedAt($value)
 *
 * @mixin Builder
 */
class File extends Model implements Responsable
{
    protected $fillable = [
        'name',
        'disk',
        'filepath',
        'filename',
        'mimetype',
        'size',
        'meta',
    ];

    protected $casts = [
        'size' => 'int',
        'meta' => 'json',
    ];

    public static function booted(): void
    {
        static::deleting(static function (self $file): bool {
            if (! $file->exists()) {
                return true;
            }

            return $file->storage()->delete($file->filepath);
        });
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function exists(): bool
    {
        return $this->exists && $this->storage()->exists($this->filepath);
    }

    public function storage(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function getNameAttribute(?string $value): string
    {
        return $value ?? Str::slug($this->basename);
    }

    public function getUrlAttribute(): ?string
    {
        return url($this->storage()->url($this->filepath));
    }

    public function getExtensionAttribute(): ?string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getBasenameAttribute(): ?string
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    public function getModifiedAtAttribute(): ?Carbon
    {
        $timestamp = $this->storage()->lastModified($this->filepath);

        if ($timestamp === null) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp);
    }

    public function toResponse($request): Response
    {
        if ($request->expectsJson()) {
            return response()->json($this->toJson());
        }

        return $this->download();
    }

    public function download(): StreamedResponse
    {
        return response()->stream(function (): void {
            $stream = $this->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => $this->mimetype,
            'Content-Length' => $this->size,
            'Content-Disposition' => 'attachment; filename="'.$this->filename.'"',
        ]);
    }

    public function stream()
    {
        return $this->storage()->readStream($this->filepath);
    }

    public function isOfMimeType(string $pattern): bool
    {
        return Str::is($pattern, $this->mimetype);
    }

    /**
     * @param string|resource $contents
     * @param array $options
     * @return bool
     */
    public function store($contents, array $options = []): bool
    {
        if ($this->fireModelEvent('storing') === false) {
            return false;
        }

        $stored = $this->storage()->put($this->filepath, $contents, $options);

        if ($stored) {
            $this->fireModelEvent('stored', false);
        }

        return $stored;
    }
}
