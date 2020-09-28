<?php

namespace Astrotomic\Fileable\Tests;

use Astrotomic\Fileable\Models\File;
use Astrotomic\Fileable\Tests\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileAdderTest extends TestCase
{
    /** @test */
    public function it_can_add_a_file_to_model(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');
        $size = filesize($original);

        $file = $post->addFile($original)->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg',
            null,
            $size,
            'image/jpeg'
        );
    }

    /** @test */
    public function it_can_add_a_symfony_file_to_model(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');
        $size = filesize($original);

        $file = $post->addFile(new SymfonyFile($original))->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg',
            null,
            $size,
            'image/jpeg'
        );
    }

    /** @test */
    public function it_can_add_an_uploaded_file_to_model(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');
        $tmp = dirname($original).'/'.Str::random();
        rename($original, $tmp);
        $size = filesize($tmp);

        $file = $post->addFile(new UploadedFile($tmp, basename($original), 'image/jpeg'))->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg',
            null,
            $size,
            'image/jpeg'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_and_preserve_original(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->preserved()->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            true,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_on_custom_disk(): void
    {
        Storage::fake('s3');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->on('s3')->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            's3',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_as_custom_filename(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->as('henry-bauer-S8DTIjQ8nPk.jpg')->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk.jpg'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_in_custom_directory(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $dir = Str::random(4);
        $file = $post->addFile($original)->in($dir)->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s/%s.jpg', $dir, $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_with_display_name(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->name('Henry Bauer - Freibergsee, Oberstdorf, Germany')->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg',
            'Henry Bauer - Freibergsee, Oberstdorf, Germany'
        );
    }

    /** @test */
    public function it_can_add_a_file_to_model_with_meta_data(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->with([
            'foo' => 'bar',
            'lorem' => 'ipsum',
        ])->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            'local',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg'
        );

        $this->assertIsArray($file->meta);
        $this->assertArrayHasKey('foo', $file->meta);
        $this->assertSame('bar', $file->meta['foo']);
        $this->assertArrayHasKey('lorem', $file->meta);
        $this->assertSame('ipsum', $file->meta['lorem']);
    }

    /** @test */
    public function it_can_add_a_file_to_model_fully_customized(): void
    {
        Storage::fake('s3');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $size = filesize($original);
        $dir = Str::random(4);
        $file = $post->addFile($original)
            ->preserved()
            ->on('s3')
            ->in($dir)
            ->as('henry-bauer-S8DTIjQ8nPk.jpg')
            ->name('Henry Bauer - Freibergsee, Oberstdorf, Germany')
            ->with([
                'foo' => 'bar',
                'lorem' => 'ipsum',
            ])
            ->save();

        $this->assertFile(
            $file,
            $post,
            $original,
            true,
            's3',
            sprintf('%s/%s.jpg', $dir, $file->uuid),
            'henry-bauer-S8DTIjQ8nPk.jpg',
            'Henry Bauer - Freibergsee, Oberstdorf, Germany',
            $size,
            'image/jpeg'
        );

        $this->assertIsArray($file->meta);
        $this->assertArrayHasKey('foo', $file->meta);
        $this->assertSame('bar', $file->meta['foo']);
        $this->assertArrayHasKey('lorem', $file->meta);
        $this->assertSame('ipsum', $file->meta['lorem']);
    }

    /** @test */
    public function it_can_add_a_file_to_model_and_tap_it_before_save(): void
    {
        Storage::fake('s3');

        /** @var Post $post */
        $post = Post::create();

        $original = self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg');

        $file = $post->addFile($original)->tap(fn (File $file) => $file->disk = 's3')();

        $this->assertFile(
            $file,
            $post,
            $original,
            false,
            's3',
            sprintf('%s.jpg', $file->uuid),
            'henry-bauer-S8DTIjQ8nPk-unsplash.jpg'
        );
    }
}
