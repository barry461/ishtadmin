<?php

/**
 * 匿名函数序列化处理
 *  使用注意。
 *      1。 避免在要序列的匿名函数中使用常量，
 *          为了提高性能，仅支持 `__FILE__` , `__DIR__`，`__LINE__` 这三个魔术常量
 *      2。序列化和反序列化 注意事项
 *          序列化时候。程序只是将 匿名函数所在的文件路径，开始行数，结束行数进行记录，
 *          然后反序列化时，通过记录的文件路径，重新生成匿名函数，所以在多机器运行，必须要求代码存放的路径一致，
 *          也可以使用 _CLOSURE_SERIALIZABLE_APP_PATH_ 常量来指明项目更目录的绝对路径，达到修复程序路径
 *
 *
 * ```php
 *
 * define('_CLOSURE_SERIALIZABLE_APP_PATH_' , APP_PATH);
 *
 * $fn = function (){
 *      echo 111,"\r\n";
 * };
 * $object1 = new ClosureSerializable($fn);
 * $str = serialize($object1);
 * echo $str,"\r\n";
 * // output ==>>> C:19:"ClosureSerializable":168:......
 * $object2 = unserialize($str);
 * $object2();
 * // ouput ===>>> 111
 *
 *
 * ```
 *
 */
class ClosureSerializable implements Serializable
{
    protected $closure;
    protected static $closureMap = [];
    protected static $codeMap = [];

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getClosure(): Closure
    {
        return $this->closure;
    }

    public function serialize(): ?string
    {
        $ref = new \ReflectionFunction($this->closure);
        $posHash = md5($ref->getFileName().':'.$ref->getStartLine().'-'
            .$ref->getEndLine());

        $scopeClass = null;
        if ($scope = $ref->getClosureScopeClass()) {
            $scopeClass = $scope->getName();
        }

        $file = $ref->getFileName();
        if (defined('_CLOSURE_SERIALIZABLE_APP_PATH_')) {
            $file = str_replace(_CLOSURE_SERIALIZABLE_APP_PATH_, '{{ROOT}}', $file);
        }

        return serialize([
            'hash'   => $posHash,
            'file'   => $file,
            'start'  => $ref->getStartLine(),
            'end'    => $ref->getEndLine(),
            'this'   => $ref->getClosureThis(),
            'static' => $ref->getStaticVariables(),
            'scope'  => $scopeClass,
        ]);
    }


    public function unserialize($data)
    {
        list('hash' => $hash, 'file' => $file, 'start' => $start,
            'this' => $object, 'static' => $static, 'scope' => $scope)
            = unserialize($data);
        $this->closure = $this->decode($hash, $file, $start, $object, $static,
            $scope);

        return $this;
    }

    public function __invoke(...$args)
    {
        return call_user_func_array($this->closure, $args);
    }

    protected function decode(
        $hash,
        $file,
        $start,
        $object,
        $static,
        $scope
    ) {
        if (!isset(self::$closureMap[$hash])) {
            if (!isset(self::$codeMap[$hash])) {
                self::$codeMap[$hash] = $this->parseCode($file, $start);
            }
            self::$closureMap[$hash]
                = $this->createClosure(self::$codeMap[$hash], $static);
        }
        $closure = self::$closureMap[$hash];
        $closure = $this->resolveClosureStatic($closure, $static);

        return Closure::bind($closure, $object, $scope);
    }

    protected function resolveClosureStatic(Closure $closure, $static)
    {
        return $closure($static);
    }

    protected function createClosure($code, $static): Closure
    {
        return $this->makeCreateFunction($static)($code);
    }

    protected function makeCreateFunction($static = []): Closure
    {
        return function ($__code) use ($static) {
            $code = 'function($static){'
                .'if (is_array($static)){extract($static);}'
                .'$__1__=%s;return $__1__;}';
            $code = sprintf($code, $__code);

            return eval("return $code;");
        };
    }


    protected function parseCode($file, $start): string
    {
        if (defined('_CLOSURE_SERIALIZABLE_APP_PATH_')) {
            $file = str_replace('{{ROOT}}', _CLOSURE_SERIALIZABLE_APP_PATH_,
                $file);
        }
        if (!file_exists($file)) {
            throw new RuntimeException('反序列化失败，代码文件未找到');
        }
        $tokens = token_get_all(file_get_contents($file));
        $code = '';
        $pos = 0;
        $quota = 0;

        $this->reduce($tokens, function ($prev, $token, $next) use (
            $file,
            &$pos,
            $start,
            &$code,
            &$quota
        ) {
            if ($pos == 0) {
                if (is_string($token) || $token[2] != $start || $token[0] != T_FUNCTION ) {
                    goto flag_end;
                }
                $pos++;
            }
            if (is_string($token)) {
                if ($token == '"') {
                    $quota += ($quota ? +1 : -1);
                }
                if ($quota) {
                    goto flag1;
                }
                if ($token == '{') {
                    $pos++;
                } elseif ($token == '}') {
                    $pos--;
                    if ($pos == 1) {
                        goto flag_exit;
                    }
                }
            } elseif (is_array($token)) {
                if ($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                    goto flag_end;
                } elseif ($token[0] == T_WHITESPACE) {
                    if (is_string($next) || is_string($prev)) {
                        goto flag_end;
                    }
                    if (isset($token[1][1])) {
                        $token[1] = ' ';
                    }
                } elseif ($token[0] == T_START_HEREDOC) {
                    $quota++;
                } elseif ($token[0] == T_END_HEREDOC) {
                    $quota--;
                } elseif ($token[0] == T_FILE) {
                    $token = '"'.$file.'"';
                } elseif ($token[0] == T_DIR) {
                    $token = '"'.dirname($file).'"';
                } elseif ($token[0] == T_LINE) {
                    $token = (string)$token[2];
                } elseif ($token[0] == T_METHOD_C) {
                    $token = '"__METHOD__"';
                } elseif ($token[0] == T_FUNC_C) {
                    $token = '"__FUNCTION__"';
                } elseif ($token[0] == T_CLASS_C) {
                    $token = '"__CLASS__"';
                } elseif ($token[0] == T_NS_C) {
                    $token = '"__NAMESPACE__"';
                } elseif ($token[0] == T_TRAIT_C) {
                    $token = '"__TRAIT__"';
                }
            }
            flag1:
            $code .= is_string($token) ? $token : $token[1];
            flag_end:
            return true;
            flag_exit:
            $code .= $token;
            return false;
        });

        return $code;
    }

    protected function reduce($tokens, $cb)
    {
        $c = count($tokens);
        for ($i = 0; $i < $c; $i++) {
            $bool = $cb($tokens[$i - 1] ?? null, $tokens[$i], $tokens[$i + 1] ?? null);
            if ($bool === false) {
                break;
            }
        }
    }

}