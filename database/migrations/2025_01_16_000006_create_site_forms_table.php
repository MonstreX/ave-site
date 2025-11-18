<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_forms', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true)->index();
            $table->integer('order')->default(0)->index();
            $table->string('title')->unique();
            $table->string('key')->unique();
            $table->longText('content')->nullable();
            $table->longText('details')->nullable(); // JSON with validator/to_address/messages/etc
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_forms');
    }
};
