<?php

namespace website\Views\js;


use Exception;

class JSParser
{
    protected $tokens;
    protected $pos = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    // 主入口：解析为语句（声明、控制结构或表达式）
    public function parse(): array
    {
        return $this->parseStatement();
    }

    public function peekEnd()
    {
        $data = [];
        for ($i = $this->pos; $i < count($this->tokens) ; $i++) {
            $data[] = $this->tokens[$i];
        }
        return $data;
    }

    // 分派不同类型语句
    public function parseStatement(): array
    {
        // let a = 1, b = 2
        if ($this->match('keyword', 'let') || $this->match('keyword', 'const') || $this->match('keyword', 'var')) {
            $this->consume();
            $decls = [];
            do {
                $id = $this->expect('identifier');
                $this->expect('operator', '=');
                $init = $this->parseExpression();
                $decls[] = [
                    'type' => 'AssignmentExpression',
                    'left' => ['type' => 'Identifier', 'name' => $id['value']],
                    'right' => $init,
                    'declaration' => true,
                ];
            } while ($this->match('operator', ',') && $this->consume());
            return ['type' => 'DeclarationList', 'declarations' => $decls];
        }
        if ($this->match('keyword', 'if')){
            return $this->parseIfStatement();
        }
        if ($this->match('keyword', 'while')) return $this->parseWhileStatement();
        if ($this->match('keyword', 'for')) return $this->parseForStatement();
        if ($this->match('keyword', 'function')) return $this->parseFunctionDeclaration();
        if ($this->match('keyword', 'return')) {
            $this->consume();
            return ['type' => 'ReturnStatement', 'argument' => $this->parseExpression()];
        }
        if ($this->match('keyword', 'throw')) {
            $this->consume();
            return ['type' => 'ThrowStatement', 'argument' => $this->parseExpression()];
        }
        if ($this->match('keyword', 'try')) return $this->parseTryCatch();
        if ($this->match('keyword', 'switch')) return $this->parseSwitchStatement();
        if ($this->match('keyword', 'class')) return $this->parseClassDeclaration();
        if ($this->match('keyword', 'import')) return $this->parseImportDeclaration();
        if ($this->match('keyword', 'export')) {
            $this->consume();
            return ['type' => 'ExportNamedDeclaration', 'declaration' => $this->parseStatement()];
        }
        if ($this->matchAry('keyword', ['true', 'false', 'null'], $value)) {
            $this->consume();
            return ['type' => 'RawStatement', 'value' => $value];
        }
//        if ($this->match('punctuator', ';' )){
//            $this->consume();
//            return ['type' => 'RawStatement', 'value' => ';'];
//        }

        // 默认当作表达式处理
        return $this->parseExpression();
    }


    // 表达式入口
    public function parseExpression(): array
    {
        return $this->parseAssignment();
    }

    // 赋值表达式
    protected function parseAssignment(): array
    {
        $left = $this->parseBinaryExpression();
        if ($this->match('operator', '=')) {
            $this->consume();
            $right = $this->parseAssignment();
            return [
                'type' => 'AssignmentExpression',
                'left' => $left,
                'right' => $right,
            ];
        }
        return $left;
    }

    // 二元/逻辑表达式
    protected function parseBinaryExpression(int $minPrecedence = 0): array
    {
        $left = $this->parseUnaryExpression();

        while ($this->match('operator')) {
            $op = $this->peek()['value'];
            $prec = $this->getPrecedence($op);
            if ($prec < $minPrecedence) break;

            $this->consume();
            $right = $this->parseBinaryExpression($prec + 1);
            $left = [
                'type' => in_array($op, ['&&', '||']) ? 'LogicalExpression' : 'BinaryExpression',
                'operator' => $op,
                'left' => $left,
                'right' => $right,
            ];
        }

        return $left;
    }

    // 一元表达式（!x, -x）
    protected function parseUnaryExpression(): array
    {
        if ($this->match('operator') && in_array($this->peek()['value'], ['!', '-', '+'])) {
            $op = $this->consume()['value'];
            return [
                'type' => 'UnaryExpression',
                'operator' => $op,
                'argument' => $this->parseUnaryExpression(),
                'prefix' => true,
            ];
        }

        return $this->parsePrimaryExpression();
    }

    // 主表达式（变量、函数调用、成员访问、字面量）
    protected function parsePrimaryExpression(): array
    {
        $token = $this->peek();

        // 字面量
        if ($token['type'] === 'number' || $token['type'] === 'string') {
            $this->consume();
            return ['type' => 'Literal', 'value' => $token['value']];
        }

        // 标识符或成员表达式
        if ($token['type'] === 'identifier') {
            $this->consume();
            $node = ['type' => 'Identifier', 'name' => $token['value']];

            // 成员访问 user.name
            while ($this->match('punctuator', '.')) {
                $this->consume();
                $prop = $this->expect('identifier');
                $node = [
                    'type' => 'MemberExpression',
                    'object' => $node,
                    'property' => $prop['value'],
                ];
            }

            // 函数调用 user.name()
            if ($this->match('punctuator', '(')) {
                $this->consume();
                $args = [];
                while (!$this->match('punctuator', ')')) {
                    $args[] = $this->parseExpression();
                    if ($this->match('punctuator', ',')) $this->consume();
                }
                $this->expect('punctuator', ')');
                return ['type' => 'CallExpression', 'callee' => $node, 'arguments' => $args];
            }

            return $node;
        }

        // 括号表达式
        if ($this->match('punctuator', '(')) {
            $this->consume();
            $expr = $this->parseExpression();
            $this->expect('punctuator', ')');
            return $expr;
        }

        throw new Exception("无法解析的表达式: " . json_encode($token));
    }

    // if 语句结构
    protected function parseIfStatement(): array
    {
        $this->consume();
        $this->expect('punctuator', '(');
        $test = $this->parseExpression();
        $this->expect('punctuator', ')');
        $consequent = $this->match('punctuator' ,'{') ? $this->parseBlock() : $this->parseStatement();
        $alternate = null;
        if ($this->match('keyword', 'else')) {
            $this->consume();
            $alternate = $this->match('punctuator' ,'{') ? $this->parseBlock() : $this->parseStatement();
        }
        return ['type' => 'IfStatement', 'test' => $test, 'consequent' => $consequent, 'alternate' => $alternate];
    }

    // while 循环
    protected function parseWhileStatement(): array
    {
        $this->consume();
        $this->expect('punctuator', '(');
        $test = $this->parseExpression();
        $this->expect('punctuator', ')');
        $body = $this->parseStatement();
        return ['type' => 'WhileStatement', 'test' => $test, 'body' => $body];
    }

    // for 循环
    protected function parseForStatement(): array
    {
        $this->consume();
        $this->expect('punctuator', '(');
        $init = $this->parseExpression();
        $this->expect('punctuator', ';');
        $test = $this->parseExpression();
        $this->expect('punctuator', ';');
        $update = $this->parseExpression();
        $this->expect('punctuator', ')');
        $body = $this->parseStatement();
        return ['type' => 'ForStatement', 'init' => $init, 'test' => $test, 'update' => $update, 'body' => $body];
    }

    // 函数声明
    protected function parseFunctionDeclaration(): array
    {
        $this->consume();
        $id = $this->expect('identifier');
        $this->expect('punctuator', '(');
        $params = [];
        while (!$this->match('punctuator', ')')) {
            $params[] = $this->expect('identifier');
            if ($this->match('punctuator', ',')) $this->consume();
        }
        $this->expect('punctuator', ')');
        $body = $this->parseBlock();
        return ['type' => 'FunctionDeclaration', 'id' => $id, 'params' => $params, 'body' => $body];
    }

    // try/catch/finally
    protected function parseTryCatch(): array
    {
        $this->consume();
        $try = $this->parseBlock();
        $this->expect('keyword', 'catch');
        $this->expect('punctuator', '(');
        $param = $this->expect('identifier');
        $this->expect('punctuator', ')');
        $catch = $this->parseBlock();
        $finally = null;
        if ($this->match('keyword', 'finally')) {
            $this->consume();
            $finally = $this->parseBlock();
        }
        return ['type' => 'TryStatement', 'block' => $try, 'handler' => ['param' => $param, 'body' => $catch], 'finalizer' => $finally];
    }

    // switch 分支结构
    protected function parseSwitchStatement(): array
    {
        $this->consume();
        $this->expect('punctuator', '(');
        $disc = $this->parseExpression();
        $this->expect('punctuator', ')');
        $this->expect('punctuator', '{');
        $cases = [];
        while (!$this->match('punctuator', '}')) {
            if ($this->match('keyword', 'case')) {
                $this->consume();
                $test = $this->parseExpression();
                $this->expect('punctuator', ':');
                $body = [];
                while (!$this->match('keyword', 'case') && !$this->match('keyword', 'default') && !$this->match('punctuator', '}')) {
                    $body[] = $this->parseStatement();
                }
                $cases[] = ['test' => $test, 'consequent' => $body];
            } elseif ($this->match('keyword', 'default')) {
                $this->consume();
                $this->expect('punctuator', ':');
                $body = [];
                while (!$this->match('keyword', 'case') && !$this->match('punctuator', '}')) {
                    $body[] = $this->parseStatement();
                }
                $cases[] = ['test' => null, 'consequent' => $body];
            }
        }
        $this->expect('punctuator', '}');
        return ['type' => 'SwitchStatement', 'discriminant' => $disc, 'cases' => $cases];
    }

    // class A { method() {} }
    protected function parseClassDeclaration(): array
    {
        $this->consume();
        $id = $this->expect('identifier');
        $this->expect('punctuator', '{');
        $methods = [];
        while (!$this->match('punctuator', '}')) {
            $key = $this->expect('identifier');
            $this->expect('punctuator', '(');
            $this->expect('punctuator', ')');
            $body = $this->parseBlock();
            $methods[] = ['key' => $key, 'value' => ['body' => $body]];
        }
        $this->expect('punctuator', '}');
        return ['type' => 'ClassDeclaration', 'id' => $id, 'body' => ['body' => $methods]];
    }

    // import 'abc'
    protected function parseImportDeclaration(): array
    {
        $this->consume();
        $this->expect('identifier');
        $this->expect('keyword', 'from');
        $src = $this->expect('string');
        return ['type' => 'ImportDeclaration', 'source' => $src];
    }

    // 语句块
    protected function parseBlock(): array
    {
        $this->expect('punctuator', '{');
        $body = [];
        while (!$this->match('punctuator', '}')) {
            $body[] = $this->parseStatement();
        }
        $this->expect('punctuator', '}');
        return ['type' => 'BlockStatement', 'body' => $body];
    }

    // ---------------- 工具方法 ----------------

    protected function peek(int $offset = 0): ?array
    {
        return $this->tokens[$this->pos + $offset] ?? null;
    }

    protected function consume(): ?array
    {
        return $this->tokens[$this->pos++] ?? null;
    }

    protected function matchAry(string $type, array $values , &$value , $offset = 0): bool
    {
        $token = $this->peek($offset);
        if (!$token || $token['type'] !== $type){
            return false;
        }
        $ok = in_array($token['value'] , $values);
        if ($ok){
            $value = $token['value'];
        }
        return $ok;
    }

    protected function match(string $type, string $value = null , $offset = 0): bool
    {
        $token = $this->peek($offset);
        if (!$token || $token['type'] !== $type){
            return false;
        }


        return $value === null || $token['value'] === $value;
    }

    protected function expect(string $type, string $value = null): array
    {
        if (!$this->match($type, $value)) {
            throw new Exception("期望 $type " . ($value ?? '') . "，但实际为 " . json_encode($this->peek()));
        }
        return $this->consume();
    }

    protected function getPrecedence(string $op): int
    {
        $map = [
            '||' => 1, '&&' => 2,
            '==' => 3, '!=' => 3, '===' => 3, '!==' => 3,
            '<' => 4, '>' => 4, '<=' => 4, '>=' => 4,
            '+' => 5, '-' => 5,
            '*' => 6, '/' => 6, '%' => 6,
        ];
        return $map[$op] ?? 0;
    }

}
