<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('key')->unique();
            $table->foreignId('region_id')->nullable()->constrained('ave_site_block_regions')->nullOnDelete();
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);

            // Visibility Rules
            $table->text('urls')->nullable(); // URL patterns (по строке на pattern)
            $table->tinyInteger('rules')->default(0); // 0=EXCEPT, 1=ONLY

            // Content
            $table->longText('content')->nullable(); // Liquid template
            $table->json('media')->nullable(); // Изображения

            // Options (JSON): DataSources, validator (для форм), и т.д.
            $table->longText('options')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_blocks');
    }
};
