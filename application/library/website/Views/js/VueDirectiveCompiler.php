<?php

namespace website\Views\js;

use website\Views\html\DomNode;
use website\Views\html\DomTextNode;

class VueDirectiveCompiler
{

    public static $components = [];

    /**
     * @throws \Exception
     */
    public static function compile(DomNode $node): string
    {
        $out = '';
        $vIf = $node->attributes['v-if'] ?? null;
        $vElseIf = $node->attributes['v-else-if'] ?? null;
        $vElse = array_key_exists('v-else', $node->attributes);
        $vFor = $node->attributes['v-for'] ?? null;
        $hasVIf = isset($node->attributes['v-if']);
        $hasVFor = isset($node->attributes['v-for']);

        if ($hasVIf && $hasVFor) {
            // 判断 v-if 写在前还是 v-for 写在前
            $attrKeys = array_keys($node->attributes);
            $first = $attrKeys[0];
            if ($first === 'v-if') {
                return self::compileForThenIf($node);
            } else {
                return self::compileForThenIf($node , false);
            }
        }

        if ($vFor !== null) {
            $out .= self::compileForThenIf($node);
            return $out;
        }

        if ($vIf !== null) {
            $expr = self::compileExpr($vIf);
            $out .= "<?php if ($expr): ?>";
        } elseif ($vElseIf !== null) {
            $expr = self::compileExpr($vElseIf);
            $out .= "<?php elseif ($expr): ?>";
        } elseif ($vElse) {
            $out .= "<?php else: ?>";
        }

        unset(
            $node->attributes['v-if'],
            $node->attributes['v-else-if'],
            $node->attributes['v-else']
        );

        // 开始渲染标签
        $out .= self::renderTag($node);
        if ($vFor !== null) {
            $out .= '<?php endforeach; ?>';
        }
        if ($vIf !== null || $vElseIf !== null || $vElse) {
            $out .= '<?php endif; ?>';
        }
        return $out;
    }

    /**
     * @throws \Exception
     */
    protected static function compileDirective(DomNode $node): string
    {
        $attrs = &$node->attributes;
        $out = '';

        // v-bind
        foreach ($attrs as $k=>$v){
            if ($k[0] == ':'){
                $expr = self::compileExpr($v);
                $attrs[substr($k , 1)] = '<?='.$expr.'?>';
                unset($attrs[$k]);
            }
        }

        if (isset($attrs['v-model'])){
            $attrs['name'] = $attrs['v-model'];
            if ($node->tag == 'textarea'){
                $node->appendChild(new DomTextNode('{{ '.$attrs['v-model'].' }}'));
            }else{
                $attrs['value'] = '<?='.self::compileExpr($attrs['v-model']).'?>';
            }
            unset($attrs['v-model']);
        }

        if (isset($attrs['v-show'])) {
            $expr = self::compileExpr($attrs['v-show']);
            $styleCode = '<?= ('.$expr.') ? "" : "display:none;" ?>';
            $attrs['style'] = ($attrs['style'] ?? '') . ";<?= $styleCode ?>";
            $attrs['style'] = trim($attrs['style'], ';');
            unset($attrs['v-show']);
        }
        if (isset($attrs['v-html'])){
            $expr = self::compileExpr($attrs['v-html']);
            if (!empty($expr)){
                $out .= '<?=('.$expr.')?>';
            }
            $node->clearChild();
            unset($attrs['v-html']);
        }
        if (isset($attrs['v-text'])){
            $expr = self::compileExpr($attrs['v-text']);
            if (!empty($expr)){
                $out .= '<?=htmlentities('.$expr.')?>';
            }
            $node->clearChild();
            unset($attrs['v-text']);
        }
        return $out;
    }

    /**
     * @throws \Exception
     */
    protected static function compileExpr($js): string
    {
        if (empty($js)) {
            return '';
        }
        $tokens = (new JSTokenizer($js))->tokenize();
        $ast = (new JSParser($tokens))->parse();

        return JSAstToPhp::toPhp($ast);
    }

    /**
     * @param  DomTextNode  $node
     *
     * @return string
     * @throws \Exception
     */
    protected static function compileText(DomTextNode $node): string
    {
        $text = $node->text;
       if (str_starts_with(strtolower($text) , '<!doctype')){
           return $text;
       }

       if ($node->parent && $node->parent->tag == 'script'){
           return $text;
       }

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', function ($m) {
            return '<?= '.self::compileExpr($m[1]).' ?>';
        }, htmlspecialchars($text));
    }


    /**
     * @throws \Exception
     */
    protected static function compileComponent(DomNode $node): string
    {
        $attrs = &$node->attributes;
        $tag = $node->tag;
        $props = [];
        foreach ($attrs as $k => $v) {
            if ($k[0] == '@'){
                continue;
            }
            $v = self::compileExpr($v);
            if ($k[0] == ':') {
                $props[] = "'".substr($k, 1)."' => $v";
                continue;
            }
            $props[] = "'$k' => $v";
        }

        $slots = [];
        foreach ($node->children as $child){
            if ($child instanceof DomNode && $child->tag === 'template') {
                foreach ($child->attributes as $name=>$value) {
                    $k = $name;
                    if (strpos($name, 'v-slot:') === 0) {
                        $name = substr($name, strlen('v-slot:'));
                        unset($child->attributes[$k]);
                        $slots[$name] = self::compile($child);
                    }elseif (strpos($name, '#') === 0) {
                        $name = substr($name, 1);
                        unset($child->attributes[$k]);
                        $slots[$name] = self::compile($child);
                    }
                }
            }
        }
        $default = '';
        foreach ($node->children as $child) {
            if ($child instanceof DomNode && $child->tag === 'template') continue;
            $default .= self::compile($child);
        }
        if ($default) {
            $slots['default'] = $default;
        }
        $slotCode = '[';
        foreach ($slots as $name=>$code){
            $slotCode .= "'$name' => function() { ?>$code<?php },";
        }
        $slotCode .= ']';
        $startTag = '<?php ';

        return $startTag
            ."\$this->component('$tag', ["
            .implode(', ', $props)
            ."], $slotCode); ?>";
    }

    /**
     * @param  array  $children
     *
     * @return string
     * @throws \Exception
     */
    public static function compileChildren(array $children): string
    {
        $out = '';
        $i = 0;
        $len = count($children);
        /** @var DomNode $child */
        while ($i < $len) {
            $child = $children[$i];
            $tag = $child->tag;
            if ($child instanceof DomTextNode){
                $out .= self::compileText($child);
                $i++;
                continue;
            }
            if (isset(self::$components[$tag])) {
                $out .= self::compileComponent($child);
                $i++;
                continue;
            }

            if ($child->tag == 'slot'){
                $name = $child->attributes['name'] ?? 'default';
                $out .= "<?php if(\$__slots['$name']) : echo call_user_func(\$__slots['$name']); else: ?> ";
                $out .= self::compileChildren($child->children);
                $out .= "<?php endif;?>";
                $i++ ;
                continue;
            }

            // v-if group start
            if (isset($child->attributes['v-if'])) {
                $group = [$child];
                $i++;
                // 收集 v-else-if / v-else
                while ($i < $len) {
                    $sibling = $children[$i];
                    if ($sibling instanceof DomTextNode) {
                        $out .= self::compileText($sibling);
                        $i++;
                        continue;
                    }
                    if (isset($sibling->attributes['v-else-if']) || isset($sibling->attributes['v-else'])) {
                        $group[] = $sibling;
                        $i++;
                    } else {
                        break;
                    }
                }
                // 编译整个 if 分支块
                if (count($group) > 1) {
                    $out .= self::compileIfGroup($group);
                    continue;
                }
                $i--;
            }
            // 普通节点
            $out .= self::compile($child);
            $i++;
        }

        return $out;
    }


    /**
     * @param  array  $group
     *
     * @return string
     * @throws \Exception
     */
    protected static function compileIfGroup(array $group): string
    {
        $out = '';
        foreach ($group as $i => $node) {
            if (isset($node->attributes['v-if'])) {
                $expr = self::compileExpr($node->attributes['v-if']);
                $out .= "<?php if ($expr): ?>";
            } elseif (isset($node->attributes['v-else-if'])) {
                $expr = self::compileExpr($node->attributes['v-else-if']);
                $out .= "<?php elseif ($expr): ?>";
            } elseif (array_key_exists('v-else', $node->attributes)) {
                $out .= "<?php else: ?>";
            }
            unset($node->attributes['v-if'], $node->attributes['v-else-if'], $node->attributes['v-else']);
            $out .= self::renderTag($node);
        }
        $out .= "<?php endif; ?>";
        return $out;
    }

    /**
     * @param  DomNode  $node
     * @param  bool  $ifFirst
     *
     * @return string
     * @throws \Exception
     */
    protected static function compileForThenIf(DomNode $node , bool $ifFirst = true): string
    {
        $vIf = self::compileExpr($node->attributes['v-if'] ?? '');
        $vFor = $node->attributes['v-for'];
        unset($node->attributes['v-if'], $node->attributes['v-for']);
        preg_match('/^\s*(\(?\s*([$]?\w+)(?:,\s*([$]?\w+))?\s*\)?)\s+in\s+(.*)$/', $vFor, $m);
        //preg_match('/^\s*(.*?)\s+in\s+(.*)$/', $vFor, $m);
        list(, , $item, $key, $expr) = $m;
        $expr = trim($expr);
        $expr = self::compileExpr($expr);
        $op = $key ? '=>' : '';
        $item = trim($item ,'$');
        $key = $op ? trim($key,'$') : '';
        if (empty($vIf)) {
            $s = "<?php if (is_iterable($expr) || is_array($expr)):?>"
                ."<?php foreach ($expr as $key$op\$$item): ?>"
                .self::renderTag($node)
                ."<?php endforeach; ?>"
                ."<?php endif; ?>";
            return $s;
        }
        if (!$ifFirst) {
            $s =
                "<?php if (is_iterable($expr) || is_array($expr)):?>"
                ."<?php foreach ($expr as $key$op\$$item): ?>"
                ."<?php if ($vIf): ?>"
                .self::renderTag($node)
                ."<?php endif;?>"
                ."<?php endforeach; ?>"
                ."<?php endif; ?>";
            return  $s;
        }

        return
            "<?php if ($vIf && (is_iterable($expr) || is_array($expr))):?>"
            ."<?php foreach ($expr as $key$op\$$item): ?>"
            .self::renderTag($node)
            ."<?php endforeach; ?>"
            ."<?php endif; ?>";
    }


    /**
     * @throws \Exception
     */
    protected static function renderTag(DomNode $node): string
    {
        if ($node instanceof DomTextNode) {
            return self::compileText($node);
        }
        $in = self::compileDirective($node);
        $out = '';
        if (!in_array($node->tag , ['template' , 'slot'])){
            $out = sprintf("<%s%s%s>", $node->tag, self::compileAttributes($node), $node->singleTag ? '/' : '');
        }

        // 内容部分
        $out .= $in;
        $out .= self::compileChildren($node->children);
        if (!in_array($node->tag , ['template' , 'slot'])){
            $out .= $node->singleTag ? '' : "</{$node->tag}>";
        }
        return $out;
    }

    protected static function compileAttributes(DomNode $node): string
    {
        $html = '';
        foreach ($node->attributes as $k => $v) {
            if (strpos($v, '<?') !== false) {
                $html .= ' '.$k.'="'.$v.'"';
            } elseif (str_starts_with($k, ':')) {
                $attr = substr($k, 1);
                $html .= ' '.$attr.'="<?= '.self::compileExpr($v).' ?>"';
            } elseif ($k === 'v-text') {
                continue; // handled later
            } else {
                $html .= ' '.$k.'="'.htmlspecialchars((string) $v).'"';
            }
        }

        return $html;
    }


}