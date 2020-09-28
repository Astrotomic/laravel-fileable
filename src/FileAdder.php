<?php

namespace Astrotomic\Fileable;

use Astrotomic\Fileable\Contracts\Fileable as FileableContract;
use Astrotomic\Fileable\Models\File;
use Closure;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    protected FilesystemFactory $filesystem;

    protected File $file;

    protected FileableContract $fileable;

    /** @var string|UploadedFile|SymfonyFile */
    protected $originalFile;

    protected ?Closure $tap = null;

    protected bool $preserveOriginal = false;

    protected ?string $directory = null;

    public function __construct(FilesystemFactory $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->file = new File();
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
     * @param string|UploadedFile|SymfonyFile $originalFile
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

    public function name(string $name): self
    {
        $this->file->name = $name;

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

    public function save(): File
    {
        $file = $this->originalFile;

        if (is_string($file)) {
            throw_unless(file_exists($file), new RuntimeException());
            throw_unless(is_readable($file), new RuntimeException());

            $this->file->filename ??= pathinfo($file, PATHINFO_BASENAME);
            $this->file->size = filesize($file);
            $this->file->mimetype = mime_content_type($file);
        }

        if ($file instanceof UploadedFile) {
            $this->file->filename ??= $file->getClientOriginalName();
            $this->file->size = $file->getSize();
            $this->file->mimetype = $file->getClientMimeType() ?? $file->getMimeType();
        }

        if ($file instanceof SymfonyFile) {
            $this->file->filename ??= $file->getFilename();
            $this->file->size = $file->getSize();
            $this->file->mimetype = $file->getMimeType();
        }

        $this->file->disk ??= config('fileable.disk');

        $this->file->filepath = implode('/', array_filter([$this->directory, $this->file->filename]));

        if ($this->tap) {
            call_user_func($this->tap, $this->file, $this->fileable, $this->originalFile);
        }

        $handle = fopen(
            is_string($file) ? $file : $file->getPathname(),
            'r'
        );
        throw_unless($this->file->store($handle), new RuntimeException());
        fclose($handle);

        if(! $this->preserveOriginal) {
            throw_unless($this->deleteOriginal(), new RuntimeException());
        }

        $this->fileable->files()->save($this->file);

        return $this->file;
    }

    public function __invoke(): File
    {
        return $this->save();
    }

    protected function deleteOriginal(): bool
    {
        if(is_string($this->originalFile)) {
            return unlink($this->originalFile);
        }

        return unlink($this->originalFile->getPathname());
    }
}
