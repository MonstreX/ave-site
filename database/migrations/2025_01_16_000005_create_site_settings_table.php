<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ave_site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->index();
            $table->longText('fields'); // JSON схема полей
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_settings');
    }
};
