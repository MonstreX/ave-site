<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ave_site_pages')) {
            return;
        }

        Schema::table('ave_site_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('ave_site_pages', 'menu')) {
                $table->boolean('menu')->default(1)->after('status');
            }
            if (!Schema::hasColumn('ave_site_pages', 'image')) {
                $table->string('image')->nullable()->after('content');
            }
            if (!Schema::hasColumn('ave_site_pages', 'images')) {
                $table->text('images')->nullable()->after('image');
            }
            if (!Schema::hasColumn('ave_site_pages', 'seo')) {
                $table->text('seo')->nullable()->after('images');
            }
            if (!Schema::hasColumn('ave_site_pages', 'details')) {
                $table->longText('details')->nullable()->after('seo');
            }
        });

        Schema::table('ave_site_pages', function (Blueprint $table) {
            $legacyColumns = ['media', 'seo_title', 'seo_description', 'seo_keywords', 'options'];

            foreach ($legacyColumns as $column) {
                if (Schema::hasColumn('ave_site_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ave_site_pages MODIFY parent_id INT NULL');
            DB::statement('ALTER TABLE ave_site_pages MODIFY `order` INT UNSIGNED NULL');
        }

        DB::table('ave_site_pages')
            ->where('parent_id', -1)
            ->update(['parent_id' => null]);
    }

    public function down(): void
    {
        //
    }
};
