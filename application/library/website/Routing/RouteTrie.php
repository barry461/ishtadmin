<?php

namespace website\Routing;

class RouteTrie
{
    protected $root = [];
    protected $pattens = [
        'id'=>'[\d]+',
    ];
    protected $matches
        = [
            '\d'   => '[\d]+',
            '\w'   => '[\w]+',
            '\d\w' => '[\d\w]+',
            '^/'   => '[^/]+',
        ];

    public function add(string $method, string $uri, array $data): void
    {
        $segments = $this->splitUri($uri);
        $current = &$this->root[$method];

        foreach ($segments as $seg) {
            $key = $seg;
            $isDynamic = false;
            $paramPattern = null;
            $hasMatch = false;
            $search = [];
            $index = 97;

            $tmp = preg_replace_callback('#\\{(\\w+)(?::(.+))?}#iUs' , function ($matches) use (&$hasMatch , &$search , &$index){
                $matches[] = null;
                list($row, $name, $pattern) = $matches;
                $pattern = $pattern ?? $this->pattens[$name] ?? '[^/]+';
                $pattern = $this->matches[$pattern] ?? $pattern;
                $hasMatch = true;
                $tmp = "@". str_repeat(chr($index) , 5) . '@';
                $search[$tmp] = "(?P<$name>$pattern)";
                $index += 1;
                return $tmp;
            } ,$seg);
            if ($hasMatch){
                $tmp = preg_quote($tmp);
                $tmp = str_replace(array_keys($search) , array_values($search) , $tmp);
                $isDynamic = true;
                $paramPattern = "#^$tmp\$#";
            }

            if (!isset($current[$key])) {
                $node = new RouteNode();
                $node->isDynamic = $isDynamic;
                $node->paramPattern = $paramPattern;
                $current[$key] = $node;
            }

            $current = &$current[$key]->children;
        }

        $leaf = new RouteNode();
        $leaf->data = $data;
        $current[':END'] = $leaf;
    }

    public function match(string $method, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === null) {
            return false;
        }
        
        // 检查 URI 是否以 / 结尾（根路径 / 除外）
        $hasTrailingSlash = $path !== '/' && substr($path, -1) === '/';
        
        $segments = $this->splitUri($uri);
        $current = $this->root[$method] ?? [];
        $params = [];
        $lastNode = null;

        foreach ($segments as $seg) {
            // 如果遇到空字符串（尾随斜杠），且当前节点已有 :END 节点，允许忽略
            if ($seg === '' && isset($current[':END'])) {
                // 直接使用 $current[':END']，因为 $current 已经是 $lastNode->children
                $leaf = $current[':END'];
                if (!empty($leaf->data)) {
                    return [
                        'data' => $leaf->data,
                        'params' => $params,
                    ];
                }
                return false;
            }
            if (isset($current[$seg])) {
                $lastNode = $current[$seg];
                $current = $lastNode->children;
                continue;
            }
            $matched = false;
            foreach ($current as $node) {
                if (!($node instanceof RouteNode) || !$node->isDynamic) {
                    continue;
                }
                if ($node->matches($seg, $tmpParams)) {
                    $params = array_merge($params, $tmpParams);
                    $lastNode = $node;
                    $current = $node->children;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }
        }
        
        // 如果匹配成功，检查是否需要尾随斜杠
        if ($lastNode instanceof RouteNode) {
            // 先检查当前节点是否有 :END（路由定义没有尾随斜杠的情况）
            $leaf = $lastNode->children[':END'] ?? null;
            if (!empty($leaf)) {
                // 路由定义没有尾随斜杠，直接匹配成功（不要求 URI 有尾随斜杠）
                return [
                    'data' => $leaf->data,
                    'params' => $params,
                ];
            }
            
            // 如果当前节点没有 :END，检查是否有空 segment 节点（路由定义有尾随斜杠的情况）
            $emptySegmentNode = $lastNode->children[''] ?? null;
            if ($emptySegmentNode instanceof RouteNode) {
                $leaf = $emptySegmentNode->children[':END'] ?? null;
                if (!empty($leaf)) {
                    // 路由定义有尾随斜杠，必须要求 URI 以 / 结尾（根路径 / 和 .html 结尾的除外）
                    if (!$hasTrailingSlash && $path !== '/' && substr($path, -5) !== '.html') {
                        return false;
                    }
                    return [
                        'data' => $leaf->data,
                        'params' => $params,
                    ];
                }
            }
            
            return false;
        }
        if (empty($segments) && isset($current[':END']) && !empty($current[':END']->data)) {
            // 根路径 / 允许不带尾随斜杠
            return [
                'data' => $current[':END']->data,
                'params' => [],
            ];
        }

        return false;
    }

    public function matchOld(string $method, string $uri)
    {
        $segments = $this->splitUri($uri);
        $current = $this->root[$method] ?? [];
        $params = [];

        foreach ($segments as $seg) {
            if (isset($current[$seg])) {
                $current = $current[$seg]->children;
            } elseif (isset($current[':param'])) {
                /** @var RouteNode $node */
                $node = $current[':param'];
                if (!$node->matches($seg, $_param)) {
                    return false;
                }
                $params = array_merge($params, $_param);
                $current = $node->children;
            } else {
                return false;
            }
        }

        if (isset($current[':END']) && $current[':END']->data) {
            return [
                'data' => $current[':END']->data,
                'params' => $params,
            ];
        }

        return false;
    }

    protected function splitUri(string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === null) {
            return [];
        }

        $hasTrailingSlash = $path !== '/' && substr($path, -1) === '/';
        // keep trailing empty segment to distinguish URLs with slash
        $path = ltrim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);
        if ($hasTrailingSlash && $segments !== []) {
            $segments[] = '';
        }
        return $segments;
    }
    public function toArray(): array
    {
        $result = [];
        foreach ($this->root as $method => $tree) {
            $result[$method] = [];
            foreach ($tree as $key => $node) {
                $result[$method][$key] = $node->toArray();
            }
        }
        return $result;
    }

    public static function fromArray(array $data): self
    {
        $trie = new self();
        foreach ($data as $method => $tree) {
            foreach ($tree as $key => $nodeData) {
                $trie->root[$method][$key] = RouteNode::fromArray($nodeData);
            }
        }
        return $trie;
    }

}
