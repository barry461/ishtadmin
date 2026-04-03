<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260306_001
{
    /**
     * 创建管理员最后编辑草稿记录表 admin_latest_draft
     */
    public function up()
    {
        if (!DB::schema()->hasTable('admin_latest_draft')) {
            DB::schema()->create('admin_latest_draft', function (Blueprint $table) {
                $table->unsignedInteger('admin_id')->comment('管理员uid');
                $table->unsignedInteger('cid')->default(0)->comment('最后编辑的草稿文章cid');
                $table->string('title', 255)->nullable()->default(null)->comment('草稿标题');
                $table->unsignedInteger('updated_at')->default(0)->comment('最后更新时间戳');

                $table->primary('admin_id');
                $table->index('cid', 'idx_admin_latest_draft_cid');
            });
        }
    }

    public function down()
    {
        if (DB::schema()->hasTable('admin_latest_draft')) {
            DB::schema()->drop('admin_latest_draft');
        }
    }
}
