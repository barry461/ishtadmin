<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20250911_001
{

    public function up()
    {
        // 以下代码等价于sql
        // alter table user add column phone varchar(20) not null after email;
        // alter table user add column address varchar(15) not null after phone;
        DB::schema()->table('user_upload' , function (Blueprint $table){
            $table->text('mp4_url')->nullable()->change();
        });
        DB::schema()->table('attachments' , function (Blueprint $table){
           $table->text('mp4_url')->nullable()->change();
        });

    }

    public function down()
    {

    }
}