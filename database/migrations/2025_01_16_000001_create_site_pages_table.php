<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_pages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->default(-1)->index(); // -1 для корневых
            $table->integer('order')->default(0);
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->json('media')->nullable(); // Изображения/файлы
            $table->boolean('status')->default(true)->index(); // 0=draft, 1=published

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            // Options (JSON): DataSources, template overrides, и т.д.
            $table->longText('options')->nullable();

            $table->timestamps();

            $table->index(['status', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_pages');
    }
};
