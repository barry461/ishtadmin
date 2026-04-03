<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260317_001
{
    /**
     * 补齐分类表排序字段，兼容线上历史库缺列场景
     */
    public function up()
    {
        if (!DB::schema()->hasTable('categories')) {
            return;
        }

        if (!DB::schema()->hasColumn('categories', 'sort_column')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->string('sort_column', 50)
                    ->default('')
                    ->after('sort_order')
                    ->comment('排序字段');
            });
        }
    }

    public function down()
    {
        if (!DB::schema()->hasTable('categories')) {
            return;
        }

        if (DB::schema()->hasColumn('categories', 'sort_column')) {
            DB::schema()->table('categories', function (Blueprint $table) {
                $table->dropColumn('sort_column');
            });
        }
    }
}
