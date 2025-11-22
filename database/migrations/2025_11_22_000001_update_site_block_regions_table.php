<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ave_site_block_regions', function (Blueprint $table) {
            // Rename 'name' to 'title' for consistency with voyager-site
            $table->renameColumn('name', 'title');
        });

        Schema::table('ave_site_block_regions', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('title');
            $table->string('color', 20)->nullable()->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('ave_site_block_regions', function (Blueprint $table) {
            $table->dropColumn(['order', 'color']);
        });

        Schema::table('ave_site_block_regions', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
        });
    }
};
