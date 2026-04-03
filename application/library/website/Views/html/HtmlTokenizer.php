<?php


namespace website\Views\html;

class HtmlTokenizer
{
    protected $html;
    protected $pos = 0;
    protected $len;
    protected $tokens = [];

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->len = strlen($html);
    }

    public function tokenize(): array
    {
        while ($this->pos < $this->len) {
            if ($this->startsWith('<!DOCTYPE')) {
                $this->pos += 9;
                $start = $this->pos;
                $this->consumeUntil('>');
                $value = trim(substr($this->html, $start, $this->pos - $start));
                $this->consume(); // >
                $this->tokens[] = [
                    'type' => 'doctype',
                    'value' => $value
                ];
                continue;
            }
            if ($this->peek() === '<') {
                if ($this->startsWith('<!--')) {
                    $this->consumeUntil('-->');
                    $this->consume(3);
                    continue;
                }

                if ($this->peek(1) === '/') {
                    $this->tokens[] = $this->readEndTag();
                } else {
                    $this->tokens[] = $this->readStartOrSelfClosingTag();
                }
            } else {
                $this->tokens[] = $this->readText();
            }
        }
        return $this->tokens;
    }

    protected function readText(): array
    {
        $start = $this->pos;
        while ($this->pos < $this->len && $this->peek() !== '<') {
            $this->pos++;
        }
        return [
            'type' => 'text',
            'content' => substr($this->html, $start, $this->pos - $start)
        ];
    }

    protected function readEndTag(): array
    {
        $this->pos += 2; // skip </
        $name = $this->readTagName();
        $this->consumeUntil('>');
        $this->pos++;
        return ['type' => 'endTag', 'name' => $name];
    }

    protected function readStartOrSelfClosingTag(): array
    {
        $this->pos++; // skip <
        $name = $this->readTagName();
        $attrs = $this->readAttributes();

        $selfClosing = false;
        if ($this->startsWith('/>')) {
            $selfClosing = true;
            $this->pos += 2;
        } else {
            $this->consumeUntil('>');
            $this->pos++;
        }

        return [
            'type' => $selfClosing ? 'selfClosingTag' : 'startTag',
            'name' => $name,
            'attributes' => $attrs
        ];
    }

    protected function readTagName(): string
    {
        $start = $this->pos;
        while (ctype_alnum($this->peek()) || $this->peek() === '-') {
            $this->pos++;
        }
        return substr($this->html, $start, $this->pos - $start);
    }

    protected function readAttributes(): array
    {
        $attrs = [];
        while (!$this->startsWith('>') && !$this->startsWith('/>') && $this->pos < $this->len) {
            $this->skipWhitespace();
            $name = $this->readWhile('/[a-zA-Z0-9_:@\-\.\#]/');
            if (!$name) break;

            $value = true;
            $this->skipWhitespace();
            if ($this->peek() === '=') {
                $this->pos++;
                $this->skipWhitespace();
                $quote = $this->peek();
                if ($quote === '"' || $quote === "'") {
                    $this->pos++;
                    $start = $this->pos;
                    while ($this->peek() !== $quote) $this->pos++;
                    $value = substr($this->html, $start, $this->pos - $start);
                    $this->pos++;
                } else {
                    $value = $this->readWhile('/[^\s>]/');
                }
            }

            $attrs[$name] = $value;
        }
        return $attrs;
    }

    protected function readWhile(string $pattern): string
    {
        $start = $this->pos;
        while ($this->pos < $this->len && preg_match($pattern, $this->peek())) {
            $this->pos++;
        }
        return substr($this->html, $start, $this->pos - $start);
    }

    protected function peek(int $offset = 0): string
    {
        return $this->html[$this->pos + $offset] ?? '';
    }

    protected function startsWith(string $str): bool
    {
        return substr($this->html, $this->pos, strlen($str)) === $str;
    }

    protected function consume(int $len = 1): void
    {
        $this->pos += $len;
    }

    protected function consumeUntil(string $end): void
    {
        while (!$this->startsWith($end) && $this->pos < $this->len) {
            $this->pos++;
        }
    }

    protected function skipWhitespace(): void
    {
        while (ctype_space($this->peek())) {
            $this->pos++;
        }
    }
}
