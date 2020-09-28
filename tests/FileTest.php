<?php

namespace Astrotomic\Fileable\Tests;

use Astrotomic\Fileable\Models\File;
use Astrotomic\Fileable\Tests\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileTest extends TestCase
{
    /** @test */
    public function it_can_delete_model_with_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();
        $file->delete();

        $this->assertFalse(Storage::disk('local')->exists($file->filepath));
        $this->assertFalse($file->exists());
        $this->assertDeleted($file);
    }

    /** @test */
    public function it_can_delete_model_without_file_in_storage(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        /** @var File $file */
        $file = $post->files()->create([
            'disk' => 'local',
            'filepath' => 'my_file.jpg',
            'filename' => 'my_file.jpg',
            'size' => 256,
            'mimetype' => 'image/jpeg',
        ]);
        $file->delete();

        $this->assertFalse(Storage::disk('local')->exists($file->filepath));
        $this->assertFalse($file->exists());
        $this->assertDeleted($file);
    }

    /** @test */
    public function it_can_get_extension_of_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $this->assertSame('jpg', $file->extension);
    }

    /** @test */
    public function it_can_get_url_of_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $this->assertSame('http://localhost/storage/henry-bauer-S8DTIjQ8nPk-unsplash.jpg', $file->url);
    }

    /** @test */
    public function it_can_get_modified_at_of_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $this->assertInstanceOf(Carbon::class, $file->modified_at);
    }

    /** @test */
    public function it_can_check_mimetype_of_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $this->assertTrue($file->isOfMimeType('image/jpeg'));
        $this->assertTrue($file->isOfMimeType('image/*'));
        $this->assertTrue($file->isOfMimeType('*/*'));
        $this->assertTrue($file->isOfMimeType('*'));
    }

    /** @test */
    public function it_can_download_file(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $response = $file->toResponse(Request::capture());
        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertSame($content, $file->storage()->get($file->filepath));
    }

    /** @test */
    public function it_can_respond_with_json(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();

        $request = Request::capture();
        $request->headers->add([
            'Accept' => 'application/json'
        ]);
        $response = $file->toResponse($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
