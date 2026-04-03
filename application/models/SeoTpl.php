<?php

use Illuminate\Database\Capsule\Manager as DB;

/**
 * class SEO模版表
 *
 * @property int $id
 * @property string $key seo模版标识
 * @property string $val seo模版
 * @property string $desc 名称
 * @property string $config SEO配置模板（变量定义）
 * @property string $mark 备注
 *
 * @mixin \Eloquent
 */
class SeoTplModel extends BaseModel
{
    protected $table = "seo_tpl";
    protected $primaryKey = 'id';
    public $timestamps = true; // 启用时间戳



    protected $fillable = [
        'key',
        'val',
        'desc',
        'config',
        'mark',
        'created_at',
        'updated_at',

    ];

    const YAC_CK_SEO = 'yac:ck:seo';
    const YAC_SE_SEO = ['key', 'val'];

    public static function seo_tpl($key): string
    {
        static $tpl = null;
        if ($tpl === null) {
            $tpl = yac()->fetch(self::YAC_CK_SEO, function () {
                return self::select(self::YAC_SE_SEO)
                    ->get()
                    ->pluck('val', 'key');
            });
        }
        return $tpl[$key] ?? '';
    }

    /**
     * 获取SEO模版配置内容（config）
     * @param string $key
     * @return string
     */
    public static function seo_config($key): string
    {
        static $config = null;
        if ($config === null) {
            $config = yac()->fetch(self::YAC_CK_SEO . ':config', function () {
                return self::select(['key', 'config'])->get()->pluck('config', 'key');
            });
        }
        return $config[$key] ?? '';
    }

    /**
     * 获取SEO模版备注内容（mark）- 纯备注说明
     * @param string $key
     * @return string
     * @deprecated 备注字段现在仅用于纯文本说明，配置内容请使用 seo_config()
     */
    public static function seo_remark($key): string
    {
        static $remark = null;
        if ($remark === null) {
            $remark = yac()->fetch(self::YAC_CK_SEO . ':remark', function () {
                return self::select(['key', 'mark'])->get()->pluck('mark', 'key');
            });
        }
        return $remark[$key] ?? '';
    }

    /**
     * 获取分页列表
     */
    public static function getPageList(array $params, int $limit, int $offset): array
    {
        $query = self::query();

        // 关键词搜索
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('key', 'like', '%' . $keyword . '%')
                    ->orWhere('desc', 'like', '%' . $keyword . '%')
                    ->orWhere('val', 'like', '%' . $keyword . '%');
            });
        }

        $total = $query->count();
        $list = $query->orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return [$list, $total];
    }

    /**
     * 获取详情
     */
    public static function getDetail(int $id)
    {
        return self::find($id);
    }

    /**
     * 保存模板(创建/更新)
     */
    public static function saveTemplate(array $data): SeoTplModel
    {
        $now = time(); // 使用时间戳，避免触发 Carbon
        
        if (!empty($data['id'])) {
            $template = self::find($data['id']);
            test_assert($template, 'SEO模板不存在');
            
            // 使用 DB 查询构建器更新，避免触发 Eloquent 的时间戳机制
            DB::table('seo_tpl')->where('id', $data['id'])->update([
                'key' => $data['key'],
                'val' => $data['val'],
                'desc' => $data['desc'] ?? '',
                'config' => $data['config'] ?? '',
                'mark' => $data['mark'] ?? '',
                'updated_at' => $now,
            ]);
            
            // 重新加载模型
            $template->refresh();
        } else {
            // 使用 DB 查询构建器插入
            $id = DB::table('seo_tpl')->insertGetId([
            'key' => $data['key'],
            'val' => $data['val'],
            'desc' => $data['desc'] ?? '',
            'config' => $data['config'] ?? '',
            'mark' => $data['mark'] ?? '',
                'created_at' => $now,
                'updated_at' => $now,
        ]);

            $template = self::find($id);
        }

        // 清除缓存
        self::clearCache();

        return $template;
    }

    /**
     * 批量删除
     */
    public static function deleteByIds(array $ids): int
    {
        $result = self::whereIn('id', $ids)->delete();

        // 清除缓存
        if ($result > 0) {
            self::clearCache();
        }

        return $result;
    }

    /**
     * 清除YAC缓存
     */
    public static function clearCache(): void
    {
        yac()->delete(self::YAC_CK_SEO);
        yac()->delete(self::YAC_CK_SEO . ':config');
        yac()->delete(self::YAC_CK_SEO . ':remark');
    }
}
