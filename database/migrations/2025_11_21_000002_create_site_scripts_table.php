<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('key')->unique();
            $table->boolean('status')->default(true);
            $table->enum('position', ['head', 'body_start', 'body_end'])->default('body_end');
            $table->text('content');
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['status', 'position', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_scripts');
    }
};
