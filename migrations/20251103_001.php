<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

/**
 * 为 permissions 表添加 sort 字段，用于菜单排序
 * 数值越大越靠前
 */
class Migration20251103_001
{
    public function up()
    {
        DB::schema()->table('permissions', function (Blueprint $table) {
            $table->addColumn('integer', 'sort', ['default' => 0, 'comment' => '排序值，数值越大越靠前']);
            $table->index('sort');
        });
    }

    public function down()
    {
        DB::schema()->table('permissions', function (Blueprint $table) {
            $table->dropIndex(['sort']);
            $table->dropColumn('sort');
        });
    }
}

