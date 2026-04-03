<?php

namespace website\Routing;

class RouteNode
{
    public $children = [];
    public $isDynamic = false;
    public $data = [];
    public $paramPattern = null;

    public function matches(string $segment, &$params = null): bool
    {
        if (!$this->isDynamic) {
            return false;
        }
        if (!$this->paramPattern) {
            return true;
        }
        if (preg_match($this->paramPattern, $segment, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    public function toArray(): array
    {
        $children = [];
        foreach ($this->children as $key => $child) {
            $children[$key] = $child->toArray();
        }
        return [
            'isDynamic' => $this->isDynamic,
            'paramPattern' => $this->paramPattern,
            'data' => $this->data,
            'children' => $children,
        ];
    }

    public static function fromArray(array $data): self
    {
        $node = new self();
        $node->isDynamic = $data['isDynamic'];
        $node->paramPattern = $data['paramPattern'];
        $node->data = $data['data'];

        foreach ($data['children'] as $key => $childData) {
            $node->children[$key] = self::fromArray($childData);
        }

        return $node;
    }

}
