<?php

namespace service;

use InternalLinkRuleArticleModel;
use InternalLinkRuleModel;
use website\Views\html\DomBuilder;
use website\Views\html\DomNode;
use website\Views\html\DomTextNode;
use website\Views\html\HtmlTokenizer;

class InternalLinkService
{
    /**
     * 对文章正文 HTML 进行站内内链自动插入。
     *
     * @param string $html 原始正文 HTML
     * @param int    $articleId 文章ID（contents.cid）
     */
    public function autoLinkContent(string $html, int $articleId): string
    {
        $articleId = (int) $articleId;
        if ($articleId <= 0 || $html === '') {
            return $html;
        }

        // 全局每篇文章最多自动插入内链数量，默认 3
        $maxPerArticle = (int) setting('internal_link_max_per_article', 3);
        if ($maxPerArticle <= 0) {
            $maxPerArticle = 3;
        }

        // 加载启用状态的规则
        $rules = InternalLinkRuleModel::query()
            ->where('status', InternalLinkRuleModel::STATUS_ENABLED)
            ->orderByDesc('priority')
            ->orderBy('inserted_article_count')
            ->get();

        if ($rules->isEmpty()) {
            return $html;
        }

        // 解析 HTML 为 DOM
        $tokens = (new HtmlTokenizer($html))->tokenize();
        $root = DomBuilder::build($tokens);

        $state = [
            'maxTotal'       => $maxPerArticle,
            'totalInserted'  => 0,
            'usedTargetUrls' => [],
            'rules'          => $rules,
            'insertedPerRule'=> [],
            'touchedRules'   => [],
        ];

        $this->walkNode($root, $state);

        // 记录“已插入文章数”
        if (!empty($state['touchedRules'])) {
            $this->recordInsertedArticles((array) $state['touchedRules'], $articleId);
        }

        return $root->innerHTML();
    }

    /**
     * 递归遍历 DOM 节点。
     *
     * @param DomNode $node
     * @param array   $state
     */
    protected function walkNode(DomNode $node, array &$state): void
    {
        if ($state['totalInserted'] >= $state['maxTotal']) {
            return;
        }

        $tag = strtolower($node->tag ?? '');
        // 禁止替换区域：a / h1~h3 / code / pre
        if (in_array($tag, ['a', 'h1', 'h2', 'h3', 'code', 'pre'], true)) {
            return;
        }

        // 遍历子节点
        $i = 0;
        while ($i < count($node->children)) {
            $child = $node->children[$i];

            if ($child instanceof DomTextNode) {
                $changedCount = $this->processTextNode($node, $child, $i, $state);
                if ($changedCount > 0) {
                    $i += $changedCount;
                    continue;
                }
            } elseif ($child instanceof DomNode) {
                $this->walkNode($child, $state);
            }

            if ($state['totalInserted'] >= $state['maxTotal']) {
                break;
            }

            $i++;
        }
    }

    /**
     * 处理文本节点，尝试在其中插入符合规则的内链。
     *
     * @return int 当前索引处被替换成多少个子节点（用于调整游标），无变更返回 0
     */
    protected function processTextNode(DomNode $parent, DomTextNode $textNode, int $index, array &$state): int
    {
        if ($state['totalInserted'] >= $state['maxTotal']) {
            return 0;
        }

        $text = (string) $textNode->text;
        if ($text === '') {
            return 0;
        }

        $bestRule = null;
        $bestPos = null;

        foreach ($state['rules'] as $rule) {
            /** @var InternalLinkRuleModel $rule */
            $ruleId = (int) $rule->id;
            $keyword = (string) $rule->keyword;
            $targetUrl = (string) $rule->target_url;

            if ($keyword === '' || $targetUrl === '') {
                continue;
            }

            // 单篇文章每个关键词只插入一次
            if (($state['insertedPerRule'][$ruleId] ?? 0) >= 1) {
                continue;
            }

            // 同一 URL 单篇文章只出现一次
            if (in_array($targetUrl, $state['usedTargetUrls'], true)) {
                continue;
            }

            $pos = $this->findWholeMatchPos($text, $keyword);
            if ($pos === null) {
                continue;
            }

            // 第一个匹配到的规则即为当前文本节点的命中规则（规则列表已按优先级排序）
            $bestRule = $rule;
            $bestPos = $pos;
            break;
        }

        if ($bestRule === null || $bestPos === null) {
            return 0;
        }

        $keyword = (string) $bestRule->keyword;
        $targetUrl = (string) $bestRule->target_url;
        $ruleId = (int) $bestRule->id;

        $before = mb_substr($text, 0, $bestPos, 'UTF-8');
        $after = mb_substr(
            $text,
            $bestPos + mb_strlen($keyword, 'UTF-8'),
            null,
            'UTF-8'
        );

        $newNodes = [];
        if ($before !== '') {
            $newNodes[] = new DomTextNode($before);
        }

        $linkNode = new DomNode('a', ['href' => $targetUrl]);
        $linkNode->appendChild(new DomTextNode($keyword));
        $newNodes[] = $linkNode;

        if ($after !== '') {
            $newNodes[] = new DomTextNode($after);
        }

        // 替换子节点
        $children = $parent->children;
        $sliceBefore = array_slice($children, 0, $index);
        $sliceAfter = array_slice($children, $index + 1);

        foreach ($newNodes as $node) {
            $node->parent = $parent;
        }

        $parent->children = array_merge($sliceBefore, $newNodes, $sliceAfter);

        $state['totalInserted']++;
        $state['insertedPerRule'][$ruleId] = ($state['insertedPerRule'][$ruleId] ?? 0) + 1;
        $state['usedTargetUrls'][] = $targetUrl;
        $state['touchedRules'][$ruleId] = true;

        return count($newNodes);
    }

    /**
     * 查找文本中关键词的“完整匹配”位置。
     *
     * 对中文关键词采用直接子串匹配；
     * 对纯英文/数字关键词增加简单的词边界判断，避免命中单词中间。
     */
    protected function findWholeMatchPos(string $text, string $keyword): ?int
    {
        if ($keyword === '') {
            return null;
        }

        // 简单判断是否为纯英文数字下划线
        $isAsciiWord = preg_match('/^[A-Za-z0-9_]+$/', $keyword) === 1;

        $offset = 0;
        $lenKeyword = mb_strlen($keyword, 'UTF-8');
        $lenText = mb_strlen($text, 'UTF-8');

        while ($offset < $lenText) {
            $pos = mb_strpos($text, $keyword, $offset, 'UTF-8');
            if ($pos === false) {
                return null;
            }

            if ($isAsciiWord) {
                $before = $pos > 0 ? mb_substr($text, $pos - 1, 1, 'UTF-8') : '';
                $afterPos = $pos + $lenKeyword;
                $after = $afterPos < $lenText ? mb_substr($text, $afterPos, 1, 'UTF-8') : '';

                if ($this->isWordBoundary($before) && $this->isWordBoundary($after)) {
                    return $pos;
                }

                $offset = $pos + $lenKeyword;
                continue;
            }

            // 中文等非 ASCII 直接认定为完整匹配
            return $pos;
        }

        return null;
    }

    protected function isWordBoundary(string $char): bool
    {
        if ($char === '') {
            return true;
        }

        return !preg_match('/[A-Za-z0-9_]/', $char);
    }

    /**
     * 记录本次在指定文章中成功插入过内链的规则，用于统计“已插入文章数”。
     *
     * @param int[] $ruleIds
     */
    protected function recordInsertedArticles(array $ruleIds, int $articleId): void
    {
        $ruleIds = array_unique(array_map('intval', $ruleIds));
        if (empty($ruleIds) || $articleId <= 0) {
            return;
        }

        foreach ($ruleIds as $ruleId) {
            if ($ruleId <= 0) {
                continue;
            }

            $exists = InternalLinkRuleArticleModel::query()
                ->where('rule_id', $ruleId)
                ->where('article_id', $articleId)
                ->exists();

            if ($exists) {
                continue;
            }

            $created = InternalLinkRuleArticleModel::create([
                'rule_id'          => $ruleId,
                'article_id'       => $articleId,
                'first_inserted_at'=> date('Y-m-d H:i:s'),
            ]);

            if ($created) {
                InternalLinkRuleModel::where('id', $ruleId)
                    ->increment('inserted_article_count');
            }
        }
    }
}

