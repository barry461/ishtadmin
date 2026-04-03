<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260305_001
{
    /**
     * 创建蜘蛛访问记录表 spiderlog
     */
    public function up()
    {
        if (!DB::schema()->hasTable('spiderlog')) {
            DB::schema()->create('spiderlog', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('spider_name', 50)->default('')->comment('蜘蛛名称，如 Baidu、Google');
                $table->string('user_agent', 255)->default('')->comment('完整 UA');
                $table->string('request_uri', 255)->default('')->comment('访问的 URI');
                $table->string('referer', 255)->default('')->comment('来源页面');
                $table->string('ip', 45)->default('')->comment('访问 IP，支持 IPv6');
                $table->string('http_method', 10)->default('GET')->comment('HTTP 方法：GET/POST 等');
                $table->smallInteger('status')->default(200)->comment('HTTP 状态码（预留字段）');
                $table->integer('created_at')->unsigned()->default(0)->comment('访问时间（UNIX 时间戳）');

                $table->index(['spider_name', 'created_at'], 'idx_spiderlog_spider_time');
                $table->index('created_at', 'idx_spiderlog_created_at');
            });
        }
    }

    public function down()
    {
        if (DB::schema()->hasTable('spiderlog')) {
            DB::schema()->drop('spiderlog');
        }
    }
}

