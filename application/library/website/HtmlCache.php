<?php

namespace {
    if (!class_exists('\Yac', false)) {
        final class Yac
        {
            function add($key, $value, $ttl = 0){}
            function get($key, &$cas = null){}
            function set($key, $value, $ttl = 0){}
            function delete($key,$delay=0){}
            function flush():bool{return !0;}
            function info():array{return [];}
            function dump($limit = 0):array{return [];}
        }
    }
}

namespace website{
    use Yac;
    use Yaf\Registry;
    use Yaf\Request_Abstract;
    use Yaf\Response\Http;
    use Yaf\Response_Abstract;

    trait HtmlCache
    {

        protected $cacheDir;
        protected $ttl = 3600;
        protected $maxTtl = 3600;
        protected $whitelist;
        protected $forceList;
        protected $enableQuery;
        protected $yac;
        protected $isIgnoreCache = null;
        protected $enable = null;
        protected $userBypass = null;
        protected $buildKey = null;
        private $internal_cache = 'internal_cache'; // 如果想修改这里, 需要修改对应nginx的配置

        public function NewHtmlCache(array $options = []) {
            $this->cacheDir = APP_PATH . '/storage/' . $this->internal_cache;
            $this->whitelist = $options['whitelist'] ?? []; // 支持正则
            $this->forceList = $options['force_list'] ?? []; // 支持正则
            $this->enableQuery = $options['enable_query'] ?? false;
            $this->enable = $options['enable'] ?? false;
            $this->ttl = $options['ttl'] ?? 3600;
            $this->maxTtl = $options['max_ttl'] ?? ($this->ttl + 120);
            $this->buildKey = $options['build_key'] ?? null;
            $this->userBypass = $options['user_bypass'] ?? function () {
                return false;
            };
            // CLI模式或Yac不可用时跳过
            if (PHP_SAPI !== 'cli' && class_exists('\\Yac', false)) {
                try {
                    $this->yac = new \Yac("html_");
                } catch (\Throwable $e) {
                    $this->yac = null;
                }
            } else {
                $this->yac = null;
            }
        }

        public function dispatchStartup() {
            if ($this->isIgnoreCache()){
                return;
            }
            Registry::set('html:join', true);
            $uri = $_SERVER['REQUEST_URI'];
            $key = $this->buildCacheKey($uri);
            $data = $this->get($key);
            if (empty($data)){
                Registry::set('html:ob_start', true);
                return;
            }
            
            // 检查缓存的状态码，
            $cachedStatusCode = $data['status_code'] ?? 200;
            if ($cachedStatusCode >= 400) {
                // 删除错误的缓存，重新生成
                $this->del($uri);
                Registry::set('html:ob_start', true);
                return;
            }
            
            $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
            if ($ifNoneMatch === $data['etag']) {
                $this->outputHeaderWithExit($key, $data, 304);
            } else {
                $this->outputHeaderWithExit($key, $data, 200);
            }
            exit;
        }

        public function dispatchShutdown(Request_Abstract $request, Response_Abstract $response)
        {
            if ($this->isIgnoreCache()){
                return;
            }
            if (Registry::get('html:skip')) {
                return;
            }
            $uri = $_SERVER['REQUEST_URI'];
            $key = $this->buildCacheKey($uri);
            $path = $this->getCacheFile($key);
            $html = $response->getBody();
            
            // 检查HTTP状态码
            $statusCode = http_response_code();
            if ($statusCode >= 400) {
                // 避免缓存错误页面
                return;
            }
            
            // 检查响应体是否为空或过小，避免缓存空白页面
            if (empty($html) || strlen(trim($html)) < 100) {
                // 响应体为空或过小，不缓存，直接返回
                return;
            }
            
            // 检查响应内容是否包含常见的错误页面标识
            $htmlLower = strtolower($html);
            if (strpos($htmlLower, '502 bad gateway') !== false || 
                strpos($htmlLower, 'bad gateway') !== false ||
                strpos($htmlLower, 'gateway timeout') !== false ||
                strpos($htmlLower, 'service unavailable') !== false) {
                // 包含错误页面标识，不缓存
                return;
            }
            
            // 使用原子写入，避免空白问题
            $tempPath = $path . '.tmp';
            $written = @file_put_contents($tempPath, $html, LOCK_EX);
            if ($written === false || $written === 0) {
                // 写入失败，不设置缓存
                @unlink($tempPath);
                return;
            }
            
            // 原子性重命名，确保文件完整性
            if (!@rename($tempPath, $path)) {
                @unlink($tempPath);
                return;
            }
            
            /** @var Http $response */
            $rawHeaders = headers_list();
            $newHeaders = $response->getHeader();
            foreach ($newHeaders as $header=>$value){
                $rawHeaders[] = "$header: $value";
            }

            $etag = '"' . md5($html) . '"';

            $data = $this->set($key, $etag, rand($this->ttl , $this->maxTtl), $rawHeaders);
            $this->outputHeaderWithExit($key , $data , 200);
            exit;
        }

        protected function outputHeaderWithExit($key, $data, $status)
        {
            $etag = $data['etag'];
            $internal_cache = $this->internal_cache;
            if(!empty($key))$internal_cache .= '/'.substr($key,0,2);
            $lastModified = gmdate('D, d M Y H:i:s', time()).' GMT';
            $expires = gmdate('D, d M Y H:i:s', time() + $this->ttl).' GMT';

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                $lastModified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            }

            if ($status == 304) {
                header('HTTP/1.1 304 Not Modified');
            } elseif(isset($data['status_code']) && $data['status_code']=="302") {
                http_response_code($data['status_code']);
            } else {
                http_response_code($data['status_code']);
                header("X-Accel-Redirect: /{$internal_cache}/".$key);
            }
            foreach ($data['headers'] as $header){
                header($header);
            }
            header("Cache-Control: public, max-age={$this->ttl}, immutable");
            header('Expires: ' . $expires);
            header('Last-Modified: '.$lastModified);
            header("ETag: {$etag}");
            exit;
        }

        protected function getCacheFile($key): string
        {
            $cachePath = $this->cacheDir.'/'.substr($key,0,2);
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0777, true);
            }
            return $cachePath.'/'.$key;
        }

        protected function isIgnoreCache(): bool
        {
            if (PHP_SAPI == "cli") {
                return true;
            }
            if (APP_MODULE != "index") {
                return true;
            }
            if (!$this->enable){
                return true;
            }
            if ($this->isIgnoreCache !== null) {
                return $this->isIgnoreCache;
            }

            $uri = $_SERVER['REQUEST_URI'];
            $uriPath = parse_url($uri, PHP_URL_PATH);

            if ($this->isForceList($uriPath)){
                return false;
            }

            $this->isIgnoreCache = $this->isWhitelisted($uriPath);
            if ($this->isIgnoreCache){
                return true;
            }
            if (is_callable($this->userBypass)){
                $this->isIgnoreCache = call_user_func($this->userBypass);
            }
            return $this->isIgnoreCache;
        }

        protected function get($key)
        {
            $htmlFile = $this->getCacheFile($key);
            
            if (is_object($this->yac) && method_exists($this->yac, 'get')) {
                $yacKey = substr(md5($key) , 8 , 20);
                $data = call_user_func([$this->yac, 'get'] , $yacKey);

                // 检查缓存过期标记
                if( $this->checkCacheExpirationtime($data) ){
                    call_user_func([$this->yac, 'delete'], $yacKey);
                    return null;
                }
                if ($data) {
                    if (!file_exists($htmlFile) || filesize($htmlFile) < 100) {
                        call_user_func([$this->yac, 'delete'], $yacKey);
                        if (is_file($htmlFile)) {
                            @unlink($htmlFile);
                        }
                        return null;
                    }
                }

                return $data;
            }
            
            $file = $htmlFile . '.mata';
            if (!file_exists($file)){
                return null;
            }

            $data = file_get_contents($file);
            $mata = json_decode($data , true);
            $ttl = $mata['ttl'] ?? 0;
            if ($ttl < time()){
                if (is_file($file)) {
                    @unlink($file);
                }
                return null;
            }

            //验证实际文件是否存在且不为空
            if (!file_exists($htmlFile) || filesize($htmlFile) < 100) {
                if (is_file($file)) {
                    @unlink($file);
                }
                if (is_file($htmlFile)) {
                    @unlink($htmlFile);
                }
                return null;
            }

            return $mata;
        }

        protected function set($key, $value, $ttl , $headers = null)
        {
            $data = [
                'uri'         => $_SERVER['REQUEST_URI'],
                'key'         => $key,
                'time'        => time(),
                'etag'        => $value,
                'ttl'         => time() + $ttl,
                'status_code' => http_response_code(),
                'headers'     => $headers ?? headers_list(),
            ];
            if (is_object($this->yac) && method_exists($this->yac, 'set')) {
                $key = substr(md5($key), 8, 20);

                call_user_func([$this->yac, 'set'], $key, $data, $ttl);
                return $data;
            }
            $file = $this->getCacheFile($key).'.mata';

            file_put_contents($file, json_encode($data));
            return $data;
        }

        public function del($uri)
        {
            $key = $this->buildCacheKey($uri);
            $file = $this->getCacheFile($key);
            if (is_file($file)) {
                @unlink($file);
            }

            $filemeta = $this->getCacheFile($key).'.mata';
            if (is_file($filemeta)) {
                @unlink($filemeta);
            }

            if (is_object($this->yac) && method_exists($this->yac, 'delete')) {
                $yacKey = substr(md5($key), 8, 20);
                call_user_func([$this->yac, 'delete'], $yacKey);
            }

            return true;
        }

        public function yacinfo()
        {
            return $this->yac ? $this->yac->info() : [];
        }

        protected function isWhitelisted($path): bool
        {
            if (empty($this->whitelist)){
                return false;
            }
            foreach ($this->whitelist as $pattern) {
                if (preg_match('#' . $pattern . '#', $path)){
                    return true;
                }
            }
            return false;
        }

        protected function isForceList($path): bool
        {
            if (empty($this->forceList)){
                return false;
            }
            foreach ($this->forceList as $pattern) {
                if (preg_match('#' . $pattern . '#', $path)){
                    return true;
                }
            }
            return false;
        }

        protected function buildCacheKey($uri): string
        {
            if ($this->buildKey) {
                $key = call_user_func($this->buildKey);
                if (is_string($key) && $key!='') {
                    return $key;
                }
            }
            if ($this->enableQuery) {
                $key = md5($uri);
            } else {
                $key = md5(parse_url($uri, PHP_URL_PATH));
            }
            return substr($key, 2, 20);
        }

        /**
         * 检查缓存过期标记
         * @return void
         */
        protected function checkCacheExpirationtime($data)
        {
            if(empty($data['time'])) return true;

            $config = Registry::get('site');
            if(!empty($config['internal_cache_expiration_time']) && strtotime($config['internal_cache_expiration_time']) !== false){
                $ex_time = strtotime($config['internal_cache_expiration_time']);
                if($data['time'] < $ex_time) return true;
            }
            return false;
        }

        public static function disableStatic()
        {
            Registry::set('html:skip', true);
        }

        public static function enableStatic()
        {
            Registry::del('html:skip');
        }

    }
}
