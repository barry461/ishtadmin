<?php

namespace commands;

use TagsModel;
use TagRelationshipsModel;
use ContentsModel;

/**
 * 清理空标签命令
 *
 * 功能：
 * 1. 查找没有关联任何文章的空标签
 * 2. 删除这些空标签及其关联关系
 * 3. 清除相关缓存
 */
class CleanEmptyTagsCommand
{
    public $signature = 'clean:empty-tags';
    public $description = '清理没有关联任何文章的空标签';

    public function handle($args)
    {
        echo "开始检查空标签...\n";

        try {
            
            // 获取所有空标签
            $emptyTags = $this->findEmptyTags();

            if (empty($emptyTags)) {
                echo "没有发现空标签。\n";
                return;
            }

            echo "发现 " . count($emptyTags) . " 个空标签：\n";

            // 执行删除
            $deletedCount = $this->deleteEmptyTags($emptyTags);
            
            echo "成功删除了 {$deletedCount} 个空标签。\n";
            echo "清理完成！\n";

        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }

    /**
     * 查找所有空标签
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function findEmptyTags()
    {
        // 查找没有关联任何已发布文章的标签
        return TagsModel::whereDoesntHave('relationships', function ($query) {
            $query->whereHas('content');
        })->limit(50000)->get();
    }

    /**
     * 删除空标签
     * 
     * @param \Illuminate\Database\Eloquent\Collection $emptyTags
     * @return int 删除的标签数量
     */
    private function deleteEmptyTags($emptyTags)
    {
        $deletedCount = 0;
        
        foreach ($emptyTags as $tag) {
            try {
                // 删除标签的所有关联关系（如果有的话）
                TagRelationshipsModel::where('tag_id', $tag->id)->delete();
                
                // 删除标签本身
                $tag->delete();
                
                // 清除相关缓存
                $this->clearTagCache($tag->id);
                
                $deletedCount++;
                echo "已删除标签: ID {$tag->id}, 名称 '{$tag->name}'\n";
                
            } catch (\Throwable $e) {
                echo "删除标签 ID {$tag->id} 时出错: " . $e->getMessage() . "\n";
            }
        }
        
        // 清除全局标签缓存
        $this->clearGlobalTagCache();
        
        return $deletedCount;
    }

    /**
     * 清除单个标签的缓存
     * 
     * @param int $tagId
     */
    private function clearTagCache($tagId)
    {
        try {
            cached(sprintf(TagsModel::CK_TAG_ID, $tagId))->clearCached();
        } catch (\Exception $e) {
            // 忽略缓存清除错误
        }
    }

    /**
     * 清除全局标签相关缓存
     */
    private function clearGlobalTagCache()
    {
        try {
            cached('tags-list-new')->clearCached();
            cached('gp:tag-detail')->clearCached();
            cached('gp:tags-list-new')->clearCached();
            cached(TagsModel::GP_TAG)->clearCached();
        } catch (\Exception $e) {
            // 忽略缓存清除错误
        }
    }
}