<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260317_002
{
    /**
     * 补齐分类表缺失字段，兼容线上历史库未同步结构的问题
     */
    public function up()
    {
        if (!DB::schema()->hasTable('categories')) {
            return;
        }
        if (!DB::schema()->hasColumn('categories', 'seo_title')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->string('seo_title', 255)
                    ->nullable()
                    ->after('created_at');
            });
        }

        if (!DB::schema()->hasColumn('categories', 'seo_keywords')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->string('seo_keywords', 255)
                    ->nullable()
                    ->after('seo_title');
            });
        }

        if (!DB::schema()->hasColumn('categories', 'seo_description')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->string('seo_description', 255)
                    ->nullable()
                    ->after('seo_keywords');
            });
        }

        if (!DB::schema()->hasColumn('categories', 'update_at')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->timestamp('update_at')
                    ->nullable()
                    ->after('seo_description');
            });
        }
    }

    public function down()
    {
        if (!DB::schema()->hasTable('categories')) {
            return;
        }

        $dropColumns = [];

        if (DB::schema()->hasColumn('categories', 'sort_column')) {
            $dropColumns[] = 'sort_column';
        }
        if (DB::schema()->hasColumn('categories', 'seo_title')) {
            $dropColumns[] = 'seo_title';
        }
        if (DB::schema()->hasColumn('categories', 'seo_keywords')) {
            $dropColumns[] = 'seo_keywords';
        }
        if (DB::schema()->hasColumn('categories', 'seo_description')) {
            $dropColumns[] = 'seo_description';
        }
        if (DB::schema()->hasColumn('categories', 'update_at')) {
            $dropColumns[] = 'update_at';
        }

        if (!empty($dropColumns)) {
            DB::schema()->table('categories', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }
}
