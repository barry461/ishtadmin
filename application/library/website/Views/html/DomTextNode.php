<?php

namespace website\Views\html;

class DomTextNode extends DomNode
{
    public $text;

    public function __construct(string $text , $tag = '__text__')
    {
        $this->text = $text;
        $this->tag = $tag;
    }

    public function outerHTML(bool $pretty = false, int $depth = 0): string
    {
        return htmlspecialchars($this->text);
    }

    public function innerHTML(): string
    {
        return $this->text;
    }
}