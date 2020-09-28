<?php

namespace Astrotomic\Fileable\Tests;

use Astrotomic\Fileable\Models\File;
use Astrotomic\Fileable\Tests\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileableTest extends TestCase
{
    /** @test */
    public function it_can_delete_model_with_related_files(): void
    {
        Storage::fake('local');

        /** @var Post $post */
        $post = Post::create();
        $file = $post->addFile(self::tempFilepath(__DIR__.'/files/henry-bauer-S8DTIjQ8nPk-unsplash.jpg'))->save();
        $post->delete();

        $this->assertDeleted($post);
        $this->assertFalse(Storage::disk('local')->exists($file->filepath));
        $this->assertFalse($file->exists());
        $this->assertDeleted($file);
    }
}
