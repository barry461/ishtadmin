<?php
namespace Tracking;

class Helper
{
    /**
     * 获取通用的埋点基础数据
     */
    private static function getBaseData()
    {
        return [
            'app_id'     => config('adscenter.app_code'),
            'channel'    => '', // 预留，如果配置有channel可在此读取
            'client_ts'  => time(),
        ];
    }

    /**
     * 获取文章浏览埋点数据
     * @param array $article 文章信息
     * @return array
     */
    public static function getArticleViewTracking($article)
    {
        $data = self::getBaseData();
        $data['event'] = 'app_page_view'; // 规范: app_page_view
        
        // 基础页面信息
        $data['page_key'] = (string)($article['cid'] ?? $article['id'] ?? '');
        $data['page_name'] = $article['title'] ?? '';
        
        // 扩展信息，供视频埋点使用 (JS端读取)
        // Category
        $data['video_type_id'] = (string)($article['category_id'] ?? 0);
        $data['video_type_name'] = $article['category']['name'] ?? '';
        
        // Tags
        $tagKeys = [];
        $tagNames = [];
        if (!empty($article['tags']) && is_array($article['tags'])) {
            foreach ($article['tags'] as $tag) {
                $tagKeys[] = $tag['mid'] ?? $tag['id'] ?? '';
                $tagNames[] = $tag['name'] ?? '';
            }
        }
        $data['video_tag_key'] = implode(',', $tagKeys);
        $data['video_tag_name'] = implode(',', $tagNames);
        
        // 生成 event_id（基于所有字段的 MD5）
        $data['event_id'] = self::generateEventId($data);
        
        return $data;
    }

    /**
     * 获取视频播放埋点数据 (如果文章内有视频)
     * @param array $article 文章信息
     * @return array
     */
    public static function getVideoPlayTracking($article)
    {
        $data = self::getBaseData();
        $data['event'] = 'video_event'; // 规范: video_event
        $data['video_action'] = 'play';
        $data['video_key'] = (string)($article['cid'] ?? $article['id'] ?? '');
        $data['video_name'] = $article['title'] ?? '';
        
        // 生成 event_id（基于所有字段的 MD5）
        $data['event_id'] = self::generateEventId($data);
        
        return $data;
    }

    /**
     * 获取关键词搜索埋点数据
     * @param string $keyword 关键词
     * @param int $count 结果数量
     * @return array
     */
    public static function getSearchTracking($keyword, $count)
    {
        $data = self::getBaseData();
        $data['event'] = 'keyword_search';
        $data['keyword'] = $keyword;
        $data['search_result_count'] = (int)$count;
        
        // 生成 event_id（基于所有字段的 MD5）
        $data['event_id'] = self::generateEventId($data);
        
        return $data;
    }

    /**
     * 生成 event_id（基于所有字段值的 MD5）
     * @param array $data 事件数据
     * @return string MD5 哈希值
     */
    private static function generateEventId($data)
    {
        // 排除 event_id 字段本身
        unset($data['event_id']);
        
        // 按键名排序，确保一致性
        ksort($data);
        
        // 提取所有值并拼接
        $values = [];
        foreach ($data as $value) {
            if ($value !== null && $value !== '') {
                $values[] = (string)$value;
            }
        }
        
        // 计算 MD5
        return md5(implode('', $values));
    }

    /**
     * 生成 Trace ID（已废弃，保留用于兼容）
     * @return string
     */
    public static function generateTraceId()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }
        
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
