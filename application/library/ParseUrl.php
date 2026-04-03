<?php

class ParseUrl
{
    protected $raw;
    protected $parts = [];

    public function __construct(string $url)
    {
        $this->raw = $url;
        $this->parts = parse_url($url) ?: [];
    }

    public static function from(string $url): self
    {
        return new self($url);
    }

    public function get(string $key, $default = null)
    {
        return $this->parts[$key] ?? $default;
    }

    public function getScheme(): ?string
    {
        return $this->parts['scheme'] ?? null;
    }

    public function getHost(): ?string
    {
        return $this->parts['host'] ?? null;
    }

    public function getPort(): ?int
    {
        return $this->parts['port'] ?? null;
    }

    public function getUser(): ?string
    {
        return $this->parts['user'] ?? null;
    }

    public function getPass(): ?string
    {
        return $this->parts['pass'] ?? null;
    }

    public function getPath(): ?string
    {
        return $this->parts['path'] ?? null;
    }

    public function getQuery(): ?string
    {
        return $this->parts['query'] ?? null;
    }

    public function getFragment(): ?string
    {
        return $this->parts['fragment'] ?? null;
    }

    public function all(): array
    {
        return $this->parts;
    }

    public function toString(): string
    {
        $url = '';
        if (isset($this->parts['scheme'])) {
            $url .= $this->parts['scheme'] . '://';
        }

        if (isset($this->parts['user'])) {
            $url .= $this->parts['user'];
            if (isset($this->parts['pass'])) {
                $url .= ':' . $this->parts['pass'];
            }
            $url .= '@';
        }

        if (isset($this->parts['host'])) {
            $url .= $this->parts['host'];
        }

        if (isset($this->parts['port'])) {
            $url .= ':' . $this->parts['port'];
        }

        if (isset($this->parts['path'])) {
            $url .= $this->parts['path'];
        }

        if (isset($this->parts['query'])) {
            $url .= '?' . $this->parts['query'];
        }

        if (isset($this->parts['fragment'])) {
            $url .= '#' . $this->parts['fragment'];
        }

        return $url;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
