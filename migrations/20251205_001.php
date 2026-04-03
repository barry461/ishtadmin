<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20251205_001
{

    public function up()
    {
        DB::schema()->table('advert', function (Blueprint $table) {
            $table->addColumn('string', 'ads_code', ['length' => 100, 'default' => '', 'comment' => '广告编码']);
        });

        // 同步数据
        DB::table('advert')
            ->update([
                'ads_code' => DB::raw("CONCAT('tj_', id)")
            ]);

        // 文章关系表
        DB::schema()->create('ads_contents',function (Blueprint $table) {
            $table->string('ads_code',100)->default('')->comment('广告编码');
            $table->integer('cid')->default(0)->comment('内容文章ID');
            //普通 index
            //$table->index('sort');
            //为两个字段创建组合索引（普通 index）
            //$table->index(['email', 'status']);
            // 自定义组合索引名称 （推荐）
            //$table->index(['email', 'status'], 'idx_email_status');
            //创建唯一组合索引（unique index）
            //$table->unique(['user_id', 'category_id'], 'uniq_user_category');
            $table->unique(['ads_code', 'cid'], 'ads_contents_aid_cid');
        });
    }

    public function down()
    {
        DB::schema()->table('advert' , function (Blueprint $table){
            $table->dropColumn('ads_code');
        });
    }
}