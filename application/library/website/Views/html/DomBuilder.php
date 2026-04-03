<?php

namespace website\Views\html;

class DomBuilder
{
    protected static $selfTags = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr'
    ];


    public static function build(array $tokens): DomNode
    {
        $root = new DomNode('root');
        $stack = [$root];

        foreach ($tokens as $token) {
            if (isset($token['name'])){
                $tag = strtolower($token['name']);
                if (in_array($tag , self::$selfTags)){
                    $token['type'] = 'selfClosingTag';
                }
            }
            switch ($token['type']) {
                case 'startTag':
//                    $isTemplate = strtolower($token['name']) === 'template';
//                    $node = $isTemplate
//                        ? new DomFragment()
//                        : new DomNode($token['name'], $token['attributes']);
                    $node = new DomNode($token['name'], $token['attributes']);
                    end($stack)->appendChild($node);
                    $stack[] = $node;
                    break;
                case 'selfClosingTag':
                    $node = new DomNode($token['name'], $token['attributes'] , in_array($tag , self::$selfTags));
                    end($stack)->appendChild($node);
                    break;
                case 'endTag':
                    array_pop($stack);
                    break;
                case 'text':
                    $text = new DomTextNode($token['content']);
                    end($stack)->appendChild($text);
                    break;
                case 'doctype':
                    $node = new DomTextNode('<!DOCTYPE ' . $token['value'] . '>' , 'doctype');
                    $root->appendChild($node);
                    break;
            }
        }

        return $root;
    }
}