<?php

namespace website\Views\html;


class DomNode
{
    public $tag;
    public $attributes = [];
    /** @var DomNode[]  */
    public $children = [];
    /** @var self  */
    public $parent = null;
    public $singleTag = false;


    public function __construct(string $tag, array $attributes = [] , $singleTag = false)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->singleTag = $singleTag;
    }

    public function appendChild(DomNode $child): void
    {
        $child->parent = $this;
        $this->children[] = $child;
        $children = $child->children;
        $child->children = [];
        foreach ($children as $node){
            $child->appendChild($node);
        }
    }

    public function clearChild()
    {
        $this->children = [];
    }

    public function remove(): void
    {
        if (!$this->parent) {
            return;
        }
        $children = array_filter($this->parent->children, function ($c) {
            return $c !== $this;
        });
        $parent = $this->parent;
        $parent->children = $children;
        $this->parent = null;
    }

    public function replaceWith(DomNode $newNode): void
    {
        if (!$this->parent) {
            return;
        }
        $i = array_search($this, $this->parent->children, true);
        if ($i !== false) {
            $newNode->parent = $this->parent;
            $this->parent->children[$i] = $newNode;
            $this->parent = null;
        }
    }

    public function hasClass(string $cls): bool
    {
        return in_array($cls, explode(' ', $this->attributes['class'] ?? ''));
    }

    public function addClass(string $cls): void
    {
        if (!$this->hasClass($cls)) {
            $this->attributes['class'] = trim(($this->attributes['class'] ?? '')
                .' '.$cls);
        }
    }

    public function removeClass(string $cls): void
    {
        $classes = explode(' ', $this->attributes['class'] ?? '');
        $this->attributes['class'] = implode(' ', array_diff($classes, [$cls]));
    }

    /**
     * @param  string  $selector
     *
     * @return DomNode[]
     */
    public function querySelectorAll(string $selector): array
    {
        $results = [];

        if (str_contains($selector, '>')) {
            [$parentSel, $childSel] = array_map('trim', explode('>', $selector));
            foreach ($this->querySelectorAll($parentSel) as $parent) {
                foreach ($parent->children as $child) {
                    if ($child instanceof DomNode && $child->matches($childSel)) {
                        $results[] = $child;
                    }
                }
            }
            return $results;
        }

        foreach ($this->children as $child) {
            if ($child instanceof DomNode) {
                if ($child->matches($selector)) {
                    $results[] = $child;
                }
                $results = array_merge($results,
                    $child->querySelectorAll($selector));
            }
        }

        return $results;
    }

    public function querySelector(string $selector): ?DomNode
    {
        return $this->querySelectorAll($selector)[0] ?? null;
    }

    public function matches(string $selector): bool
    {
        if (preg_match('/^(\w+):nth-child\((\d+)\)$/', $selector, $m)) {
            return $this->tag === $m[1]
                && $this->getChildIndex() === (int) $m[2];
        }

        if ($selector === $this->tag) {
            return true;
        }
        if (str_starts_with($selector, '.')
            && $this->hasClass(substr($selector, 1))) {
            return true;
        }

        if (preg_match('/^\[([\w\-]+)="(.*?)"\]$/', $selector, $m)) {
            return ($this->attributes[$m[1]] ?? null) === $m[2];
        }

        return false;
    }

    protected function getChildIndex(): int
    {
        if (!$this->parent) {
            return -1;
        }
        $siblings = array_values(array_filter($this->parent->children,
            function ($c) {
                return $c instanceof DomNode && $c->tag === $this->tag;
            }));

        return array_search($this, $siblings, true) + 1;
    }

    public function innerHTML(): string
    {
        $html = '';
        /** @var DomNode $child */
        foreach ($this->children as $child) {
            if ($child instanceof DomTextNode){
                if ($child->tag != '__text__') {
                    $html .= $child->text;
                } else {
                    $html .= htmlspecialchars($child->text ?? '');
                }
            }else{
                $html .= $child->outerHTML();
            }
        }
        return $html;
    }


    public function setInnerHTML($html)
    {
        $this->clearChild();
        $tokens = (new HtmlTokenizer($html))->tokenize();
        $dom = DomBuilder::build($tokens);
        /** @var DomNode $child */
        foreach ($dom->children as $child){
            $this->appendChild($child);
        }
    }



    public function outerHTML(bool $pretty = false, int $depth = 0): string
    {
        $indent = $pretty ? str_repeat("  ", $depth) : '';
        $attrStr = '';
        foreach ($this->attributes as $k => $v) {
            $attrStr .= " $k=\"".htmlspecialchars((string) $v)."\"";
        }

        $html = $indent."<{$this->tag}$attrStr";
        if ($this->singleTag){
            $html .= "/>";
        }else{
            $html .= ">";
        }


        foreach ($this->children as $child) {
            $html .= $child instanceof DomNode
                ? $child->outerHTML($pretty, $depth + 1)
                : htmlspecialchars($child->text ?? '');
        }

        if (!$this->singleTag){
            $html.="</{$this->tag}>";
        }
        return $html;
    }
}
