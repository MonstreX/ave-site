<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ave_site_localizations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('en')->nullable();
            $table->text('ru')->nullable();
            $table->timestamps();

            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ave_site_localizations');
    }
};
