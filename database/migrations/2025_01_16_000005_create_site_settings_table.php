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
            $table->integer('order')->unsigned()->default(0); // Sort order in admin menu
            $table->string('title')->nullable(); // Display title in admin menu
            $table->string('key')->unique(); // Unique identifier (general, mail, seo, theme)
            $table->string('group')->index(); // Grouping (currently same as key)
            $table->longText('fields'); // JSON schema with all fields
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ave_site_settings');
    }
};
