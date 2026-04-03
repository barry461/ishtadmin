<?php


namespace helper;


use Illuminate\Database\Query\Builder;
use Yaf\Application;

/**
 * Class QueryHelper
 * @package App\library\helper
 * @author xiongba
 * @date 2019-11-02 15:23:44
 */
class QueryHelper
{

    /**
     * @param Builder $builder
     * @param string $offsetName
     * @param string $limitName
     * @return Builder
     * @author xiongba
     * @date 2019-11-02 15:21:37
     */
    public function pagination($builder, $offsetName = 'pageNumber', $limitName = 'pageSize')
    {
        /** @var \Yaf\Request\Http $request */
        $request = Application::app()->getDispatcher()->getRequest();
        //当前多少页
        $offset = $request->get($offsetName, 1);
        $offset = $offset <= 1 ? 0 : $offset - 1;
        //每页限时
        $limit = $request->get($limitName, 10);
        return $builder->offset($offset * $limit)->limit($limit);
    }


    /**
     * 返回分页参数
     * @param int $defaultLimit
     * @param string $pageName
     * @param string $limitName
     * @return array [$page, $limit , $last_ix]
     */
    public static function pageLimit(int $defaultLimit = 20, string $pageName = 'page', string $limitName = 'limit'): array
    {
        $data = $_POST;
        //当前多少页
        $page = $_POST[$pageName] ?? 1;
        $page = $page <= 1 ? 1 : $page;
        //每页限时
        $limit = (int)($_POST[$limitName] ?? $defaultLimit);
        if ($limit > 50){
            $limit = 50;
        }
        if ($limit < 1){
            $limit = 1;
        }
        $last_ix = $data['last_ix'] ?? null;
        if (empty($last_ix)) {
            $last_ix = null;
        }
        return [$page, $limit, $last_ix];
    }


    /**
     * @param Builder $builder
     * @param \Closure $iteration 原型 function($item){return $item;}
     * @return array
     * @author xiongba
     * @date 2019-11-02 15:21:37
     */
    public function bootstrapTable($builder, \Closure $iteration)
    {
        $srcBuilder = clone $builder;
        $result = $this->pagination($builder, 'pageNumber', 'pageSize')->get();
        $data = [];
        if (!empty($result)) {
            foreach ($result as $item) {
                $data[] = $iteration($item);
            }
        }
        $result = [
            'total' => empty($data) ? 0 : $srcBuilder->count(),
            'rows'  => $data,
            'code'  => 200
        ];;
        return $result;
    }

    /**
     * @param Builder $builder
     * @param \Closure $iteration 原型 function($item){return $item;}
     * @return array
     * @author xiongba
     * @date 2019-11-02 15:21:37
     */
    public function layuiTable($builder, \Closure $iteration)
    {
        $srcBuilder = clone $builder;
        $result = $this->pagination($builder, 'page', 'limit')->get();
        $data = [];
        if (!empty($result)) {
            foreach ($result as $item) {
                $data[] = $iteration($item, $result);
            }
        }
        $result = [
            'count' => 1,
            'data'  => $data,
            "msg"   => '',
            'code'  => 0
        ];;
        return $result;

    }


}