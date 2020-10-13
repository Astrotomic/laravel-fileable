<?php

namespace Astrotomic\Fileable;

use Astrotomic\Fileable\Contracts\File as FileContract;
use Astrotomic\Fileable\Contracts\Fileable as FileableContract;
use Astrotomic\Fileable\Models\File;
use Closure;
use finfo;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use League\Flysystem\Util\MimeType;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class FileAdder
{
    protected FilesystemFactory $filesystem;

    /** @var FileContract|Model  */
    protected FileContract $file;

    /** @var FileableContract|Model */
    protected FileableContract $fileable;

    /** @var string|UploadedFile|SymfonyFile|resource */
    protected $originalFile;

    protected ?Closure $tap = null;

    protected bool $preserveOriginal = false;

    protected ?string $directory = null;

    public function __construct(FilesystemFactory $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->file = app(config('fileable.file_model', File::class));
    }

    public function to(FileableContract $fileable): self
    {
        $this->fileable = $fileable;

        return $this;
    }

    public function on(string $disk): self
    {
        $this->file->disk = $disk;

        return $this;
    }

    public function in(string $directory): self
    {
        $this->directory = rtrim($directory, '/');

        return $this;
    }

    /**
     * @param string|UploadedFile|SymfonyFile|resource $originalFile
     *
     * @return $this
     */
    public function add($originalFile): self
    {
        $this->originalFile = $originalFile;

        return $this;
    }

    public function as(string $filename): self
    {
        $this->file->filename = $filename;

        return $this;
    }

    public function named(string $name): self
    {
        $this->file->display_name = $name;

        return $this;
    }

    public function with(array $meta): self
    {
        $this->file->meta = $meta;

        return $this;
    }

    public function tap(Closure $callback): self
    {
        $this->tap = $callback;

        return $this;
    }

    public function preserved(bool $preserve = true): self
    {
        $this->preserveOriginal = $preserve;

        return $this;
    }

    public function save(): FileContract
    {
        $this->fillFile();

        $this->file->disk ??= config('fileable.disk');

        $this->file->setUuid($this->file->generateUniqueUuid());

        $this->file->filepath = implode('/', array_filter([
            $this->directory,
            $this->file->uuid.'.'.pathinfo($this->file->filename, PATHINFO_EXTENSION),
        ]));

        if ($this->tap) {
            call_user_func($this->tap, $this->file, $this->fileable, $this->originalFile);
        }

        $this->storeFile();

        throw_unless(
            $this->fileable->exists,
            (new ModelNotFoundException())->setModel(get_class($this->fileable))
        );

        $this->fileable->files()->save($this->file);

        return $this->file;
    }

    public function __invoke(): FileContract
    {
        return $this->save();
    }

    protected function storeFile()
    {
        $handle = fopen(
            is_string($this->originalFile)
                ? $this->originalFile
                : $this->originalFile->getPathname(),
            'r'
        );
        throw_unless($this->file->store($handle), new RuntimeException());
        fclose($handle);

        if (! $this->preserveOriginal) {
            throw_unless($this->deleteOriginal(), new RuntimeException());
        }
    }

    protected function deleteOriginal(): bool
    {
        if (is_string($this->originalFile)) {
            return unlink($this->originalFile);
        }

        return unlink($this->originalFile->getPathname());
    }

    protected function fillFile(): FileContract
    {
        if (is_resource($this->originalFile)) {
            return $this->fillFileFromStream($this->originalFile);
        } elseif (is_string($this->originalFile)) {
            return $this->fillFileFromPath($this->originalFile);
        } elseif ($this->originalFile instanceof UploadedFile) {
            return $this->fillFileFromUploadedFile($this->originalFile);
        } elseif ($this->originalFile instanceof SymfonyFile) {
            return $this->fillFileFromSymfonyFile($this->originalFile);
        }

        throw new OutOfBoundsException('Unsupported original file passed to FileAdder.');
    }

    protected function fillFileFromPath(string $path): FileContract
    {
        throw_unless(file_exists($path), new RuntimeException());
        throw_unless(is_readable($path), new RuntimeException());

        $this->file->filename ??= pathinfo($path, PATHINFO_BASENAME);
        $this->file->size = filesize($path);
        $this->file->mimetype = mime_content_type($path);

        return $this->file;
    }

    protected function fillFileFromUploadedFile(UploadedFile $file): FileContract
    {
        $this->file->filename ??= $file->getClientOriginalName();
        $this->file->size = $file->getSize();
        $this->file->mimetype = $file->getClientMimeType() ?? $file->getMimeType();

        return $this->file;
    }

    protected function fillFileFromSymfonyFile(SymfonyFile $file): FileContract
    {
        $this->file->filename ??= $file->getFilename();
        $this->file->size = $file->getSize();
        $this->file->mimetype = $file->getMimeType();

        return $this->file;
    }

    /**
     * @param resource $file
     *
     * @return FileContract
     */
    protected function fillFileFromStream($file): FileContract
    {
        $content = stream_get_contents($file);
        $finfo = new finfo(FILEINFO_MIME);

        $this->file->size = strlen($content);
        $this->file->mimetype = $finfo->buffer($content);

        $this->file->filename ??= sprintf(
            '%s-%s.%s',
            date('Ymd_His'),
            Str::random(),
            MimeTypes::getDefault()->getExtensions($this->file->mimetype)
        );

        return $this->file;
    }
}
