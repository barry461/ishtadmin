<?php

namespace commands;

use Exception;
use Illuminate\Database\Capsule\Manager;
use TagRelationshipsModel;

/**
 * 清理重复标签命令
 *
 * 功能：
 * 1. 查找重复的标签（基于name字段）
 * 2. 保留最早创建的标签
 * 3. 将重复标签的关联关系更新到保留的标签
 * 4. 删除重复的标签
 */
class CleanDuplicateTagsCommand
{
    public $signature = 'clean:duplicate-tags';
    public $description = '清理重复的标签并更新关联关系';

    public function handle($args)
    {
        echo "开始清理重复标签...\n";

        try {
            // 获取所有重复的标签
            $duplicateTags = $this->findDuplicateTags();

            if (empty($duplicateTags)) {
                echo "没有发现重复的标签。\n";
                return;
            }

            echo "发现 " . count($duplicateTags) . " 组重复标签。\n";

            $totalProcessed = 0;
            $totalDeleted = 0;
            $totalUpdated = 0;

            foreach ($duplicateTags as $tagName => $tags) {
                $result = $this->processDuplicateGroup($tagName, $tags);
                $totalProcessed++;
                $totalDeleted += $result['deleted'];
                $totalUpdated += $result['updated'];

                echo "处理标签 '{$tagName}': 删除 {$result['deleted']} 个重复标签, 更新 {$result['updated']} 个关联关系,删除 {$result['delete_duplication_relations']} 个重复关联关系。\n";
            }

            echo "\n清理完成！\n";
            echo "总计处理: {$totalProcessed} 组重复标签\n";
            echo "总计删除: {$totalDeleted} 个重复标签\n";
            echo "总计更新: {$totalUpdated} 个关联关系\n";

        } catch (Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
            echo "清理过程中断。\n";
        }
    }

    /**
     * 查找重复的标签
     * @return array 返回重复标签的分组数组
     */
    private function findDuplicateTags()
    {
        // 使用 Eloquent ORM 查找重复标签
        $duplicateTagNames = \TagsModel::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name')
            ->toArray();

        $duplicates = [];

        foreach ($duplicateTagNames as $tagName) {
            // 获取每个重复标签名的所有标签，按创建时间排序
            $tags = \TagsModel::where('name', $tagName)
                ->orderBy('created_at', 'asc')
                ->get();

            $duplicates[$tagName] = $tags;
        }

        return $duplicates;
    }

    /**
     * 处理一组重复的标签
     * @param string $tagName 标签名称
     * @param \Illuminate\Database\Eloquent\Collection $tags 标签集合（已按创建时间排序）
     * @return array 返回处理结果统计
     * @throws Exception
     */
    private function processDuplicateGroup($tagName, $tags): array
    {
        // 保留第一个（最早创建的）标签
        $keepTag = $tags->first();
        $duplicateTags = $tags->slice(1);

        $deletedCount = 0;
        $updatedCount = 0;
        $deleteDuplicateCount = 0;

        // 开始数据库事务
        Manager::beginTransaction();
        try {
            // 更新关联关系
            foreach ($duplicateTags as $duplicateTag) {
                // 查找该重复标签的所有关联关系
                $relationships = TagRelationshipsModel::where('tag_id', $duplicateTag->id)->get();

                foreach ($relationships as $relationship) {
                    //查询当前关联文章是否关联了保留的标签
                    $exits = TagRelationshipsModel::where('tag_id', $keepTag->id)->where('cid', $relationship->cid)->exists();
                    if ($exits) {
                        TagRelationshipsModel::where('cid', $relationship->cid)->where('tag_id', $relationship->tag_id)->delete();
                        $deleteDuplicateCount++;
                    } else {
                        TagRelationshipsModel::where('tag_id', $relationship->tag_id)->where('cid',$relationship->cid)->update(['tag_id' => $keepTag->id]);
                        $updatedCount++;
                    }
                }

                // 删除重复的标签
                $duplicateTag->delete();
                $deletedCount++;
            }

            // 提交事务
            Manager::commit();

        } catch (Exception $e) {
            // 回滚事务
            Manager::rollback();
            throw $e;
        }

        return [
            'deleted' => $deletedCount,
            'updated' => $updatedCount,
            'delete_duplication_relations' => $deleteDuplicateCount,
        ];
    }

    /**
     * 获取命令签名
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * 获取命令描述
     */
    public function getDescription()
    {
        return $this->description;
    }
}