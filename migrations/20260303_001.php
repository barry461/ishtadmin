<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20260303_001
{
    /**
     * 创建用于管理统计与验证代码的 seo_stat_code 表
     */
    public function up()
    {

       if (!DB::schema()->hasTable('seo_stat_code')) {
           DB::schema()->create('seo_stat_code', function (Blueprint $table) {
               $table->increments('id');
               $table->string('name', 191)->default('')->comment('配置名称，例如 GA4、GTM、验证代码说明');
               $table->enum('position', ['head', 'footer'])->default('head')->comment('插入位置：head=</head>前，footer=</body>前');
               $table->text('code')->comment('统计或验证代码内容，HTML/JS 片段');
               $table->tinyInteger('status')->default(1)->comment('状态：0=禁用，1=启用');
               $table->integer('sort')->default(0)->comment('排序，越小越靠前');
               $table->timestamps();

               $table->index(['position', 'status', 'sort'], 'idx_seo_stat_position_status_sort');
           });
       }
    }

    public function down()
    {
        DB::schema()->dropIfExists('seo_stat_code');
    }
}

