<?php

namespace website\Views\html;

class DomFragment extends DomNode
{
    public function __construct()
    {
        parent::__construct('__fragment__');
    }

    public function outerHTML(bool $pretty = false, int $depth = 0): string
    {
        $html = '';
        foreach ($this->children as $child) {
            $html .= $child instanceof DomNode
                ? $child->outerHTML($pretty, $depth)
                : htmlspecialchars($child->text ?? '');
        }
        return $html;
    }

    public function isFragment(): bool
    {
        return true;
    }
}