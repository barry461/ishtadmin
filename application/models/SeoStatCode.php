<?php

use Illuminate\Database\Capsule\Manager as DB;

/**
 * 统计与验证代码表
 *
 * @property int $id
 * @property string $name 配置名称
 * @property string $position 插入位置 head/footer
 * @property string $code 代码内容
 * @property int $status 状态 0=禁用 1=启用
 * @property int $sort 排序
 * @property int $created_at
 * @property int $updated_at
 *
 * @mixin \Eloquent
 */
class SeoStatCodeModel extends BaseModel
{
    protected $table = 'seo_stat_code';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'position',
        'code',
        'status',
        'sort',
        'created_at',
        'updated_at',
    ];

    /**
     * 按位置获取所有启用的统计/验证代码
     *
     * @param string $position head|footer
     * @return array
     */
    public static function allByPosition(string $position): array
    {
        $position = $position === 'footer' ? 'footer' : 'head';

        $rows = self::query()
            ->where('position', $position)
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'position', 'code', 'status', 'sort']);

        if ($rows->isEmpty()) {
            return [];
        }

        return $rows->toArray();
    }

    /**
     * 按位置获取已拼接好的 HTML 片段
     *
     * @param string $position
     * @return string
     */
    public static function renderByPosition(string $position): string
    {
        $items = self::allByPosition($position);
        if (empty($items)) {
            return '';
        }

        $codes = array_column($items, 'code');

        return implode("\n", array_filter($codes, static function ($code) {
            return trim((string)$code) !== '';
        }));
    }
}

