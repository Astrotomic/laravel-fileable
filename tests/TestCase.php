<?php

namespace Astrotomic\Fileable\Tests;

use Astrotomic\Fileable\Contracts\Fileable as FileableContract;
use Astrotomic\Fileable\FileableServiceProvider;
use Astrotomic\Fileable\Models\File;
use DirectoryIterator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File as FileHelper;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    protected function tearDown(): void
    {
        /** @var DirectoryIterator $item */
        foreach (new DirectoryIterator(__DIR__.'/tmp') as $item) {
            if ($item->isDot()) {
                continue;
            }
            if ($item->getFilename() === '.gitignore') {
                continue;
            }

            $item->isDir()
                ? FileHelper::deleteDirectory($item->getPathname())
                : FileHelper::delete($item->getPathname());
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            FileableServiceProvider::class,
        ];
    }

    public static function assertFile(
        File $file,
        FileableContract $fileable,
        string $original,
        bool $preserved,
        ?string $disk,
        string $filepath,
        ?string $filename = null,
        ?string $basename = null,
        ?string $name = null,
        ?int $size = null,
        ?string $mimetype = null
    ): void {
        $filename ??= pathinfo($filepath, PATHINFO_BASENAME);
        $basename ??= pathinfo($filename, PATHINFO_FILENAME);
        $name ??= Str::slug($basename);

        static::assertTrue($file->exists());
        static::assertTrue($file->fileable->is($fileable));
        static::assertSame($preserved, file_exists($original));
        static::assertSame($disk, $file->disk);
        static::assertSame($filepath, $file->filepath);
        static::assertSame($filename, $file->filename);
        static::assertSame($filename, $file->filename);
        static::assertSame($basename, $file->basename);
        static::assertSame($name, $file->name);

        if ($size === null) {
            static::assertIsInt($file->size);
            static::assertGreaterThan(0, $file->size);
        } else {
            static::assertSame($size, $file->size);
        }

        if ($mimetype === null) {
            static::assertIsString($file->mimetype);
        } else {
            static::assertSame($mimetype, $file->mimetype);
        }
    }

    protected static function tempFilepath(string $filepath): string
    {
        $target = __DIR__.'/tmp/'.Str::random(8).'/'.pathinfo($filepath, PATHINFO_BASENAME);
        @mkdir(dirname($target), 0755, true);
        copy($filepath, $target);

        return $target;
    }
}
