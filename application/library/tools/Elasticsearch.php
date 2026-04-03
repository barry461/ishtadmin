<?php

namespace tools;

use Elasticsearch\ClientBuilder;
use Yaf\Registry;

class Elasticsearch
{
    private static $client;
    private static $instance;
    public static $index = 'porn_video';
    public static $hosts = [];

    private function __construct()
    {
        ini_set('arg_separator.output', '&');
        self::$client = ClientBuilder::create()->setHosts(self::$hosts)->build();
    }

    public static function registerConfig(array $hosts)
    {
        self::$hosts = $hosts;
    }

    private static function init(): Elasticsearch
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function table(string $index): Elasticsearch
    {
        $hosts = self::$hosts[0] ?? self::$hosts;
        $index = sprintf("%s%s", $hosts['table_prefix'] ?? '', $index);
        self::$index = $index;

        return self::init();
    }


    public static function space($table, \Closure $closure, ...$args)
    {
        if (empty($table)) {
            throw new \RuntimeException('es的index不能为空');
        }
        $index = self::$index;
        self::table($table);
        try {
            $result = $closure(...$args);
        } finally {
            self::$index = $index;
        }

        return $result;
    }

    /**
     * 在{table}空间里面执行匿名函数，遇见异常时候，直接停止匿名函数的执行，并且将异常丢弃
     * 执行完成后。将会恢复之前的{table}空间
     *
     * ```php
     *
     * LibEs::spaceTry("mv" , fn(){
     *    // 这里 table 为 mv
     *    LibEs::delete(1); // 删除 mv 下面id=1的数据
     *
     *    LibEs::spaceTry("topic" , fn(){
     *          // 这里 table 为 topic
     *          LibEs::delete(1); // 删除 topic 下面id=1的数据
     *    });
     *    // 这里 table 为 mv
     *    LibEs::delete(2); // 删除 mv 下面id=2的数据
     * })
     *
     * ```
     *
     * @param $table
     * @param \Closure $closure
     * @param ...$args
     *
     * @return mixed|void
     */
    public static function spaceTry($table, \Closure $closure, ...$args)
    {
        try {
            return self::space($table, $closure, ...$args);
        } catch (\Throwable $e) {
        }
    }

    private static function client(): \Elasticsearch\Client
    {
        self::init();

        return self::$client;
    }

    public static function exists($id): bool
    {
        $params = [
            'index' => self::$index,
            'id'    => $id,
        ];

        return self::client()->exists($params);
    }


    public static function updateOrCreate($data, $primaryKey = 'id')
    {
        $id = $data[$primaryKey];
        if (self::exists($id)) {
            return self::update($data, $primaryKey);
        } else {
            return self::create($data, $primaryKey);
        }
    }

    public static function create(array $data, string $primaryKey = 'id')
    {
        $esBody['index'] = self::$index;
        $esBody['body'][] = [
            'create' => [
                '_id' => $data[$primaryKey],
            ],
        ];
        $esBody['body'][] = $data;

        return self::client()->bulk($esBody);
    }

    public static function createArray(array $data, string $primaryKey = 'id')
    {
        $esBody['index'] = self::$index;
        $esBody['body'] = [];
        $count = 0;
        foreach ($data as $datum) {
            $esBody['body'][] = [
                'create' => ['_id' => $datum[$primaryKey]],
            ];
            $esBody['body'][] = $datum;
            ++$count;
            if ($count % 100 === 0) {
                self::client()->bulk($esBody);
                $esBody['body'] = [];
                $count = 0;
            }
        }
        self::client()->bulk($data);
    }

    public static function update(array $data, string $primaryKey = 'id')
    {
        $body[] = ['update' => ['_id' => $data[$primaryKey]]];
        $body[] = ['doc' => $data];

        $esBody['index'] = self::$index;
        $esBody['body'] = $body;

        return self::client()->bulk($esBody);
    }

    public static function delete($id)
    {
        $param = [
            'index' => self::$index,
            'id'    => $id,
        ];

        return self::client()->delete($param);
    }

    public static function tryDelete($id)
    {
        try {
            self::delete($id);
        } catch (\Throwable $e) {
        }
    }

    public static function search(
        string $keywords,
        int $offset = 0,
        int $limit = 15
    ): array {
        $params = [
            'index' => self::$index,
            'from'  => $offset,
            'size'  => $limit,
            'body'  => [
                'query' => [
                    'bool' => [
                        'should'               => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'match_phrase' => [
                                                'nickname' => [
                                                    'query' => "{$keywords}",
                                                    'slop'  => 10,
                                                ],
                                            ],
                                        ],
                                        [
                                            'match_phrase' => [
                                                'tags' => [
                                                    'query' => "{$keywords}",
                                                    'slop'  => 15,
                                                ],
                                            ],
                                        ],
                                        [
                                            'match_phrase' => [
                                                'title' => [
                                                    'query' => "{$keywords}",
                                                    'slop'  => 20,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'adjust_pure_negative' => true,
                                    'boost' => 20,
                                ],
                            ]
                            ,
                            ['match' => ['title' => $keywords]],
                            ['match' => ['nickname' => $keywords]],
                            ['match' => ['tags' => $keywords]],
                        ],
                        'adjust_pure_negative' => true,
                    ],
                ],
            ],
        ];
        $result = self::client()->search($params);
        $temp = [];
        if (isset($result['hits']['hits'])
            && count($result['hits']['hits']) > 0
        ) {
            foreach ($result['hits']['hits'] as $hit) {
                isset($hit['_source']['fanhao'])
                && $hit['_source']['_id'] = $hit['_source']['fanhao'];
                $temp[] = $hit['_source'];
            }
        }

        return $temp;
    }


    /**
     * @param $index
     * @param string $field
     * @param string|int $value
     * @param int $page
     * @param int $size
     * @param array $option
     *
     * @return array|callable
     */
    public static function match(
        string $field,
        $value,
        int $page,
        int $size = 10,
        array $option = []
    ) {
        $query = [
            'match' => [
                $field => $value,
            ],
        ];

        return self::queryRaw($query, $page, $size, $option);
    }


    public static function queryRaw(
        array $query,
        int $page,
        int $size = 10,
        array $option = []
    ) {
        $from = (($page <= 1) ? 0 : $page - 1) * $size;
        $params = [
            'index' => self::$index,
            'size'  => $size,
            'from'  => $from,
            'body'  => [
                'query' => $query,
            ],
        ];
        $params = array_merge($params, $option);

        // hits.hits
        return self::client()->search($params);
    }


    public static function get(int $id)
    {
        $params = [
            'index' => self::$index,
            'id'    => $id,
        ];

        return self::client()->get($params);
    }

    public static function querySql(string $query, int $limit = 5000): array
    {
        $hosts = self::$hosts[0] ?? self::$hosts;
        $query = preg_replace_callback("#@\{([^\}]*)\}#i", function ($v) use ($hosts) {
            return ($hosts['table_prefix'] ?? '').$v[1];
        }, $query);

        return self::client()->sql()->query([
            'format' => 'JSON',
            'body'   => ['query' => $query, 'fetch_size' => $limit],
        ]);
    }

}