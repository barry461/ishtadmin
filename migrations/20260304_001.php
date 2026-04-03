<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260304_001
{
    /**
     * 创建站内内链相关表：
     * - internal_link_rule         内链规则表
     * - internal_link_rule_article 规则与文章关联表（用于统计已插入文章数）
     */
    public function up()
    {
        if (!DB::schema()->hasTable('internal_link_rule')) {
            DB::schema()->create('internal_link_rule', function (Blueprint $table) {
                $table->increments('id');
                $table->string('keyword', 191)->default('')->comment('关键词（正文中需要匹配的文字）');
                $table->string('target_url', 255)->default('')->comment('指向链接，相对路径，例如 /seo-plugin');
                $table->integer('max_per_article')->default(1)->comment('单篇最多插入次数（当前逻辑固定为1，预留扩展）');
                $table->integer('priority')->default(0)->comment('优先级，数字越大越优先');
                $table->integer('inserted_article_count')->default(0)->comment('已插入文章数（缓存字段，配合日志表统计）');
                $table->tinyInteger('status')->default(1)->comment('状态：1=启用，0=暂停');
                $table->timestamps();

                $table->index(['status', 'priority'], 'idx_internal_link_rule_status_priority');
                $table->index('keyword', 'idx_internal_link_rule_keyword');
            });
        }

        if (!DB::schema()->hasTable('internal_link_rule_article')) {
            DB::schema()->create('internal_link_rule_article', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('rule_id')->unsigned()->default(0)->comment('内链规则ID');
                $table->integer('article_id')->unsigned()->default(0)->comment('文章ID（contents.cid）');
                $table->timestamp('first_inserted_at')->useCurrent()->comment('第一次在该文章中成功插入时间');

                $table->unique(['rule_id', 'article_id'], 'uniq_internal_link_rule_article');
                $table->index('rule_id', 'idx_internal_link_rule_article_rule');
                $table->index('article_id', 'idx_internal_link_rule_article_article');
            });
        }
    }

    public function down()
    {
        if (DB::schema()->hasTable('internal_link_rule_article')) {
            DB::schema()->drop('internal_link_rule_article');
        }
        if (DB::schema()->hasTable('internal_link_rule')) {
            DB::schema()->drop('internal_link_rule');
        }
    }
}

