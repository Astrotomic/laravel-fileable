<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    public function up(): void
    {
        Schema::create('files', static function (Blueprint $table): void {
            $table->id();
            $table->morphs('fileable');
            $table->string('name')->nullable();
            $table->string('disk');
            $table->string('filepath');
            $table->string('filename');
            $table->string('mimetype');
            $table->unsignedBigInteger('size');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['disk', 'filepath']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
}
