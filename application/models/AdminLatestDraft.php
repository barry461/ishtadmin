<?php

/**
 * 管理员最后编辑的草稿记录（独立表，不修改 contents 大表）
 *
 * @property int $admin_id 管理员uid
 * @property int $cid 最后编辑的草稿文章cid
 * @property string|null $title 草稿标题
 * @property int $updated_at 最后更新时间戳
 *
 * @mixin \Eloquent
 */
class AdminLatestDraftModel extends BaseModel
{
    protected $table = 'admin_latest_draft';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['admin_id', 'cid', 'title', 'updated_at'];

    /**
     * Redis 缓存相关常量
     */
    private const REDIS_KEY_PREFIX = 'admin:latest_draft:';
    private const REDIS_TTL = 86400 * 7; // 7 天即可，避免无限膨胀

    /**
     * 最近一次从文章列表进入编辑的文章（仅用于新后台“继续编辑xxx文章”入口，纯 Redis，不落库）
     */
    private const REDIS_LAST_EDIT_PREFIX = 'admin:last_edit_article:';
    private const REDIS_LAST_EDIT_TTL = 86400 * 7;

    /**
     * 生成 Redis Key
     */
    private static function getRedisKey(int $adminId): string
    {
        return self::REDIS_KEY_PREFIX . $adminId;
    }

    /**
     * 将最新草稿写入 Redis
     */
    private static function saveToRedis(int $adminId, int $cid, string $title, int $updatedAt): void
    {
        if (!function_exists('redis')) {
            return;
        }

        $payload = [
            'admin_id'   => $adminId,
            'cid'        => $cid,
            'title'      => $title,
            'updated_at' => $updatedAt,
        ];

        try {
            redis()->setex(
                self::getRedisKey($adminId),
                self::REDIS_TTL,
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::saveToRedis] 异常: ' . $e->getMessage());
            }
        }
    }

    /**
     * 从 Redis 获取最新草稿，命中返回数组，否则返回 null
     */
    public static function getLatestFromRedis(int $adminId): ?array
    {
        if (!function_exists('redis')) {
            return null;
        }

        try {
            $raw = redis()->get(self::getRedisKey($adminId));
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::getLatestFromRedis] 异常: ' . $e->getMessage());
            }
            return null;
        }

        if ($raw === false || $raw === null || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['cid'])) {
            return null;
        }

        return $data;
    }

    /**
     * 从 Redis 中删除某管理员的缓存
     */
    private static function deleteRedisCache(int $adminId): void
    {
        if (!function_exists('redis')) {
            return;
        }

        try {
            redis()->del(self::getRedisKey($adminId));
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::deleteRedisCache] 异常: ' . $e->getMessage());
            }
        }
    }

    /**
     * 生成「最近编辑文章」的 Redis Key
     */
    private static function getLastEditRedisKey(int $adminId): string
    {
        return self::REDIS_LAST_EDIT_PREFIX . $adminId;
    }

    /**
     * 记住最近一次从文章列表进入编辑的文章（仅 Redis）
     */
    public static function rememberLastEditArticle(int $adminId, int $cid, string $title = '', ?int $timestamp = null): void
    {
        if (!function_exists('redis')) {
            return;
        }

        $now = $timestamp ?? time();
        $payload = [
            'admin_id'   => $adminId,
            'cid'        => $cid,
            'title'      => $title,
            'updated_at' => $now,
        ];

        try {
            redis()->setex(
                self::getLastEditRedisKey($adminId),
                self::REDIS_LAST_EDIT_TTL,
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::rememberLastEditArticle] 异常: ' . $e->getMessage());
            }
        }
    }

    /**
     * 获取最近一次从文章列表进入编辑的文章（仅 Redis）
     */
    public static function getLastEditArticleFromRedis(int $adminId): ?array
    {
        if (!function_exists('redis')) {
            return null;
        }

        try {
            $raw = redis()->get(self::getLastEditRedisKey($adminId));
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::getLastEditArticleFromRedis] 异常: ' . $e->getMessage());
            }
            return null;
        }

        if ($raw === false || $raw === null || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['cid'])) {
            return null;
        }

        return $data;
    }

    /**
     * 清除最近编辑文章的 Redis 记录
     */
    public static function clearLastEditArticle(int $adminId): void
    {
        if (!function_exists('redis')) {
            return;
        }

        try {
            redis()->del(self::getLastEditRedisKey($adminId));
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::clearLastEditArticle] 异常: ' . $e->getMessage());
            }
        }
    }

    /**
     * 带前缀的草稿表名（与 typecho_contents 同前缀，如 typecho_admin_latest_draft）
     */
    public static function getDraftTableName(): string
    {
        $prefix = '';
        if (class_exists('Yaf_Registry') && Yaf_Registry::has('database')) {
            $db = Yaf_Registry::get('database');
            $prefix = isset($db->prefix) ? (string) $db->prefix : '';
        }
        return $prefix . 'admin_latest_draft';
    }

    /**
     * 记录/更新某管理员最后编辑的草稿（含标题，单表即可查最新草稿）
     */
    public static function setLatestDraft(int $adminId, int $cid, string $title = ''): void
    {
        if (function_exists('draft_log')) {
            $m = (new static());
            draft_log('[AdminLatestDraftModel::setLatestDraft] 入参 adminId=' . $adminId . ', cid=' . $cid . ', title=' . $title . ', 表名=' . $m->getTable());
        }
        $now = time();
        try {
            // 1) 写入数据库（作为持久化兜底）
            self::updateOrInsert(
                ['admin_id' => $adminId],
                ['cid' => $cid, 'title' => $title, 'updated_at' => $now]
            );
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::setLatestDraft] updateOrInsert 执行完成');
            }

            // 2) 同步写入 Redis，加速后续读取
            self::saveToRedis($adminId, $cid, $title, $now);
        } catch (\Throwable $e) {
            if (function_exists('draft_log')) {
                draft_log('[AdminLatestDraftModel::setLatestDraft] 异常: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            }
            throw $e;
        }
    }

    /**
     * 移除某管理员对某文章的草稿记录（草稿变为发布状态时调用）
     * 使用带前缀的表名直接删除，确保命中 typecho_admin_latest_draft
     */
    public static function removeDraft(int $adminId, int $cid): void
    {
        $table = self::getDraftTableName();
        \DB::statement(
            'DELETE FROM `' . str_replace('`', '``', $table) . '` WHERE `admin_id` = ? AND `cid` = ?',
            [$adminId, $cid]
        );

        // 同步清理 Redis 缓存
        self::deleteRedisCache($adminId);
    }

    /**
     * 按文章 cid 列表移除草稿记录（删除文章或批量改为发布等非草稿状态时调用）
     */
    public static function removeByCids(array $cids): void
    {
        if (empty($cids)) {
            return;
        }
        $cids = array_map('intval', $cids);
        $table = self::getDraftTableName();
        $placeholders = implode(',', array_fill(0, count($cids), '?'));
        \DB::statement(
            'DELETE FROM `' . str_replace('`', '``', $table) . '` WHERE `cid` IN (' . $placeholders . ')',
            $cids
        );
    }
}
