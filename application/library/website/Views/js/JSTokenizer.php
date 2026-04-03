<?php

namespace website\Views\js;

use Exception;

class JSTokenizer
{
    protected $code;
    protected $pos = 0;
    protected $length;
    protected $tokens = [];

    // 常见关键字
    protected $keywords = [
        'if', 'else', 'for', 'while', 'return', 'function',
        'let', 'const', 'var', 'switch', 'case', 'default',
        'break', 'continue', 'try', 'catch', 'finally', 'throw',
        'import', 'export', 'class', 'new', 'await', 'async', 'from',
        'true', 'false', 'null'
    ];

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->length = strlen($code);
    }

    // 主入口：返回 Token 数组
    public function tokenize(): array
    {
        while ($this->pos < $this->length) {
            $char = $this->peek();

            // 跳过空格与换行
            if (ctype_space($char)) {
                $this->consume();
                continue;
            }

            // 数字（整数、小数）
            if (ctype_digit($char)) {
                $this->tokens[] = $this->readNumber();
                continue;
            }

            // 标识符 / 关键字
            if (ctype_alpha($char) || $char === '_' || $char == '$') {
                $this->tokens[] = $this->readIdentifierOrKeyword();
                continue;
            }

            // 字符串：支持 " 和 '
            if ($char === '"' || $char === "'") {
                $this->tokens[] = $this->readString();
                continue;
            }

            // 操作符（多字符：===, !==, <= 等）
            if (strpbrk($char, '=!<>+-*/%&|')) {
                $this->tokens[] = $this->readOperator();
                continue;
            }

            // 括号、符号（单字符）：(){}.,;:等
            if (in_array($char, ['(', ')', '{', '}', '[', ']', '.', ',', ';', ':'])) {
                $this->tokens[] = [
                    'type' => 'punctuator',
                    'value' => $char
                ];
                $this->consume();
                continue;
            }

            throw new Exception("无法识别的字符：$char");
        }

        return $this->tokens;
    }

    // 读取数字字面量（支持小数）
    protected function readNumber(): array
    {
        $start = $this->pos;
        while (ctype_digit($this->peek())) {
            $this->consume();
        }
        if ($this->peek() === '.') {
            $this->consume(); // .
            while (ctype_digit($this->peek())) {
                $this->consume();
            }
        }
        $num = substr($this->code, $start, $this->pos - $start);
        return ['type' => 'number', 'value' => (float)$num];
    }

    // 读取字符串（支持双引号或单引号包裹）
    protected function readString(): array
    {
        $quote = $this->consume();
        $start = $this->pos;
        while ($this->pos < $this->length && $this->peek() !== $quote) {
            if ($this->peek() === '\\') {
                $this->consume(); // 跳过转义符
                $this->consume();
            } else {
                $this->consume();
            }
        }
        $value = stripslashes(substr($this->code, $start, $this->pos - $start));
        $this->consume(); // 结束引号
        return ['type' => 'string', 'value' => $value];
    }

    // 读取标识符或关键字
    protected function readIdentifierOrKeyword(): array
    {
        $start = $this->pos;
        while (ctype_alnum($this->peek()) || $this->peek() === '_'|| $this->peek() === '$') {
            $this->consume();
        }
        $id = substr($this->code, $start, $this->pos - $start);

        if (in_array($id, $this->keywords)) {
            return ['type' => 'keyword', 'value' => $id];
        }

        return ['type' => 'identifier', 'value' => $id];
    }

    // 读取操作符（支持多字符组合）
    protected function readOperator(): array
    {
        $ops = ['===', '!==', '==', '!=', '<=', '>=', '&&', '||', '=>'];
        foreach ($ops as $op) {
            if (substr($this->code, $this->pos, strlen($op)) === $op) {
                $this->pos += strlen($op);
                return ['type' => 'operator', 'value' => $op];
            }
        }

        // 单字符操作符
        $op = $this->consume();
        return ['type' => 'operator', 'value' => $op];
    }

    // 读取当前位置字符
    protected function peek(int $offset = 0): ?string
    {
        return $this->code[$this->pos + $offset] ?? null;
    }

    // 消耗一个字符
    protected function consume(): ?string
    {
        return $this->code[$this->pos++] ?? null;
    }
}
