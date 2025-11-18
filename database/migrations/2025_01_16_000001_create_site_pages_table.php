<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable()->index();
            $table->boolean('status')->default(1)->index();
            $table->boolean('menu')->default(1);
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->text('images')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('seo')->nullable();
            $table->longText('details')->nullable();
            $table->timestamps();

            $table->index(['status', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_pages');
    }
};
