<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;


class Migration20250924_001
{

    public function up()
    {/*
        DB::schema()->table('custom_sort' , function (Blueprint $table){
            $table->integer('status')->default(0)->comment('状态 0=关闭');
        });

        DB::schema()->table('contents' , function (Blueprint $table){
            $field1 = 'sort_field1';
            $table->integer($field1)->default(0)->comment('预设排序字段1');
            $table->index($field1);

            $field2 = 'sort_field2';
            $table->integer($field2)->default(0)->comment('预设排序字段2');
            $table->index($field2);

            $field3 = 'sort_field3';
            $table->integer($field3)->default(0)->comment('预设排序字段3');
            $table->index($field3);

            $field4 = 'sort_field4';
            $table->integer($field4)->default(0)->comment('预设排序字段4');
            $table->index($field4);

            $field5 = 'sort_field5';
            $table->integer($field5)->default(0)->comment('预设排序字段5');
            $table->index($field5);

            $field6 = 'sort_field6';
            $table->integer($field6)->default(0)->comment('预设排序字段6');
            $table->index($field6);
        });


        DB::table( 'custom_sort' )->insert([
            ['name' => '排序1',  'slug' => 'sort_field1'],
            ['name' => '排序2',  'slug' => 'sort_field2'],
            ['name' => '排序3',  'slug' => 'sort_field3'],
            ['name' => '排序4',  'slug' => 'sort_field4'],
            ['name' => '排序5',  'slug' => 'sort_field5'],
            ['name' => '排序6',  'slug' => 'sort_field6'],
        ]);
        */
    }

    public function down()
    {

    }
}