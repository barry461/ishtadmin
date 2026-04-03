<?php

namespace website\Views\js;

use Exception;

class JSAstToPhp
{
    public static function toPhp($node): string
    {
        switch ($node['type']) {
            case 'Literal':
                if (is_numeric($node['value'])){
                    return $node['value'];
                }
                return var_export($node['value'], true);
            case 'Identifier':
                return "\${$node['name']}";
            case 'RawStatement':
                return $node['value'];
            case 'IfStatement':
                $code = 'if('.self::toPhp($node['test']).'){'.self::toPhp($node['consequent']).'}';
                if ($node['alternate']) {
                    $code .= 'else{'.self::toPhp($node['alternate']).'}';
                }
                return $code ;
            case 'BinaryExpression':
            case 'LogicalExpression':
                return '(' . self::toPhp($node['left']) . ' ' . $node['operator'] . ' ' . self::toPhp($node['right']) . ')';
            case 'UnaryExpression':
                return '(' . $node['operator'] . ' ' . self::toPhp($node['argument']) . ')';
            case 'AssignmentExpression':
                if ($node['left']['type'] === 'Identifier') {
                    return '$' . $node['left']['name'] . ' = ' . self::toPhp($node['right']);
                }
                throw new Exception("Unsupported assignment target");

            case 'ConditionalExpression':
                return '(' . self::toPhp($node['test']) . ' ? ' . self::toPhp($node['consequent']) . ' : ' . self::toPhp($node['alternate']) . ')';

            // 支持对象简写和展开 {...obj, a}
            case 'ObjectExpression':
                $entries = [];
                foreach ($node['properties'] as $prop) {
                    if ($prop['type'] === 'SpreadElement') {
                        $entries[] = '...' . self::toPhp($prop['argument']);
                    } else {
                        $key = var_export($prop['key'] ?? $prop['value']['name'], true);
                        $value = self::toPhp($prop['value'] ?? ['type' => 'Identifier', 'name' => $prop['key']]);
                        $entries[] = "$key => $value";
                    }
                }
                return '[' . implode(', ', $entries) . ']';

            // 支持数组展开语法 [...arr, x]
            case 'ArrayExpression':
                $items = [];
                foreach ($node['elements'] as $el) {
                    if ($el['type'] === 'SpreadElement') {
                        $items[] = '...' . self::toPhp($el['argument']);
                    } else {
                        $items[] = self::toPhp($el);
                    }
                }
                return '[' . implode(', ', $items) . ']';

            case 'MemberExpression':
                $parts = [];
                while ($node['type'] === 'MemberExpression') {
                    $parts[] = is_array($node['property']) ? $node['property']['name'] : $node['property'];
                    $node = $node['object'];
                }
                $parts[] = $node['name'];
                $parts = array_reverse($parts);
                $baseVar = '$' . array_shift($parts);
                return "data_get($baseVar, '" . implode('.', $parts) . "')";

            case 'CallExpression':
                $callee = $node['callee'];
                $args = array_map([self::class, 'toPhp'], $node['arguments'] ?? []);
                if ($callee['type'] === 'MemberExpression') {
                    $method = is_array($callee['property']) ? $callee['property']['name'] : $callee['property'];
                    $target = self::toPhp($callee['object']);
                    array_unshift($args, $target);
                    $map = ['trim' => 'trim', 'toLowerCase' => 'strtolower', 'toUpperCase' => 'strtoupper', 'length' => 'strlen'];
                    if (!isset($map[$method])) throw new Exception("Unsupported method call: $method()");
                    return $map[$method] . '(' . implode(', ', $args) . ')';
                }
                if ($callee['type'] === 'Identifier') {
                    return $callee['name'] . '(' . implode(', ', $args) . ')';
                }
                throw new Exception("Unsupported callee");

            case 'TemplateLiteral':
                $parts = [];
                foreach ($node['quasis'] as $i => $quasi) {
                    $parts[] = var_export($quasi['value'], true);
                    if (isset($node['expressions'][$i])) {
                        $parts[] = self::toPhp($node['expressions'][$i]);
                    }
                }
                return implode(' . ', $parts);

            // 箭头函数表达式：x => x + 1
            case 'ArrowFunctionExpression':

                $params = implode(', ', array_map(function ($p){
                    return '$' . $p['name'];
                } , $node['params']));
                $body = $node['body']['type'] === 'BlockStatement'
                    ? self::toPhp($node['body'])
                    : 'return ' . self::toPhp($node['body']) . ';';
                return 'function(' . $params . ') {' . $body . '}';

            case 'ClassDeclaration':
                $class = $node['id']['name'];
                $fn = function ($m){
                    'public function ' . $m['key']['name'] . '() { ' . self::toPhp($m['value']['body']) . ' }';
                };
                $body = implode("\n", array_map($fn, $node['body']['body']));
                return "class $class {\n$body\n}";

            case 'ImportDeclaration':
                return 'require_once ' . var_export($node['source']['value'], true) . ';';

            case 'ExportNamedDeclaration':
                return self::toPhp($node['declaration']);

            case 'TryStatement':
                $try = self::toPhp($node['block']);
                $param = '$' . $node['handler']['param']['name'];
                $catch = self::toPhp($node['handler']['body']);
                $finally = isset($node['finalizer']) ? self::toPhp($node['finalizer']) : '';
                return "try {\n$try\n} catch ($param) {\n$catch\n}" . ($finally ? " finally {\n$finally\n}" : '');

            case 'ThrowStatement':
                return 'throw new Exception(' . self::toPhp($node['argument']) . ');';

            case 'FunctionDeclaration':
                $name = $node['id']['name'];
                $params = implode(', ', array_map(function ($p){
                    return '$' . $p['name'];
                }, $node['params']));
                $body = self::toPhp($node['body']);
                return "function $name($params) {\n$body\n}";

            case 'WhileStatement':
                return 'while (' . self::toPhp($node['test']) . ") {\n" . self::toPhp($node['body']) . "\n}";

            case 'ForStatement':
                return 'for (' . self::toPhp($node['init']) . '; ' . self::toPhp($node['test']) . '; ' . self::toPhp($node['update']) . ") {\n" . self::toPhp($node['body']) . "\n}";

            case 'SwitchStatement':
                $cases = '';
                foreach ($node['cases'] as $case) {
                    $label = $case['test'] ? 'case ' . self::toPhp($case['test']) : 'default';
                    $body = implode("\n", array_map([self::class, 'toPhp'], $case['consequent']));
                    $cases .= "$label:\n$body\nbreak;\n";
                }
                return 'switch (' . self::toPhp($node['discriminant']) . ") {\n$cases}";
            case 'AwaitExpression':
                return self::toPhp($node['argument']);
            // 自增自减表达式 i++ / ++i
            case 'UpdateExpression':
                $arg = '$' . $node['argument']['name'];
                return $node['prefix'] ? $node['operator'] . $arg : $arg . $node['operator'];
            case 'BreakStatement':
                return 'break;';
            case 'ContinueStatement':
                return 'continue;';
            case 'BlockStatement':
                return implode("\n", array_map([self::class, 'toPhp'], $node['body']));
            case 'DeclarationList':
                return implode("\n", array_map(function ($d){
                    self::toPhp($d) . ';';
                }, $node['declarations']));
            default:
                throw new Exception("Unsupported AST node type: {$node['type']}");
        }
    }
}
