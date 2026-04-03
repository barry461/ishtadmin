<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20251031_001
{

    public function up()
    {
        // 添加 config 字段到 seo_tpl 表
        DB::schema()->table('seo_tpl', function (Blueprint $table) {
            $table->text('config')->nullable()->comment('SEO配置模板（变量定义）')->after('val');
        });

        // 数据迁移：将现有 mark 字段中的配置内容迁移到 config 字段
        $records = DB::table('seo_tpl')->select(['id', 'mark'])->get();
        
        foreach ($records as $record) {
            if (empty($record->mark)) {
                continue;
            }
            
            // 检测是否包含配置格式（{变量名} = 值）
            if (preg_match('/\{[^}]+\}\s*=/', $record->mark)) {
                // 将配置内容迁移到 config 字段
                DB::table('seo_tpl')
                    ->where('id', $record->id)
                    ->update([
                        'config' => $record->mark,
                        'mark' => '' // 清空 mark，后续可手动填写纯备注
                    ]);
            }
        }
    }

    public function down()
    {
        // 回滚：将 config 内容迁移回 mark，然后删除 config 字段
        $records = DB::table('seo_tpl')->select(['id', 'config', 'mark'])->get();
        
        foreach ($records as $record) {
            if (!empty($record->config)) {
                // 将 config 内容迁移回 mark
                $markContent = !empty($record->mark) ? $record->mark . "\n\n" . $record->config : $record->config;
                DB::table('seo_tpl')
                    ->where('id', $record->id)
                    ->update(['mark' => $markContent]);
            }
        }
        
        // 删除 config 字段
        DB::schema()->table('seo_tpl', function (Blueprint $table) {
            $table->dropColumn('config');
        });
    }
}

