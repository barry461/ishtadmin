<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20250919_001
{

    public function up()
    {
//        DB::schema()->table('categories' , function (Blueprint $table){
//            $table->string('sort_column',50)->default('')->after('sort_order')->comment('排序字段');
//        });

    }

    public function down()
    {
    }
}