<?php


namespace repositories;


use helper\QueryHelper;
use Illuminate\Database\Query\Builder;
use PackageModel;
use Yaf\Application;
use Yaf\Response_Abstract;

/**
 * Trait HoutaiRepository
 * @package repositories
 * @author xiongba
 */
trait HoutaiRepository
{

    protected $_model;

    /**
     * ajax获取列表数据时，获取到的数据将会递归走一次本函数，数据回调处理
     * 重写本方法以达到逐条处理数据库查询出来的数据
     * @return \Closure
     */
    protected function listAjaxIteration():\Closure
    {
        return function ($item) {
            /** @var \Illuminate\Database\Eloquent\Model $item */
            $result = $item->toArray();
            return $result;
        };
    }


    /**
     * ajax获取列表时初始化的where条件
     * 重写本方法以达到ajax获取列表时使用的sql where条件
     * @return array
     */
    protected function listAjaxWhere()
    {
        return [];
    }


    /**
     * 搜索时候的like条件
     * @return array
     */
    protected function getSearchLikeParam()
    {
        $get = $_GET;
        $get['search'] = $get['search'] ?? [];
        $where = [];
        foreach ($get['search'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            $value = $this->formatSearchVal($key, $value);
            list($key,$value) = $this->formatKey($key,$value);
            if (empty($key)) {
                continue;
            }
            $where[] = [$key, 'like', "$value%"];
        }
        return $where;
    }

    /**
     * 搜索时候的like条件
     * @return array
     */
    protected function getSearchDoubleLikeParam()
    {
        $get = $_GET;
        $get['like'] = $get['like'] ?? [];
        $where = [];
        foreach ($get['like'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            $value = $this->formatSearchVal($key, $value);
            list($key,$value) = $this->formatKey($key,$value);
            if (empty($key)) {
                continue;
            }
            $where[] = [$key, 'like', "%$value%"];
        }
        return $where;
    }


    /**
     * 格式化要搜索的字符串
     * @param $columnName
     * @param $val
     * @return string
     */
    protected function formatSearchVal($columnName, $val)
    {
        return trim($val);
    }

    protected function argsEmpty($v): bool
    {
        if (empty($v) || $v === '__undefined__') {
            return true;
        }
        return false;
    }

    /**
     * 搜索范围条件构造
     * ?between[column][from]=1&between[column][to]=100
     * @return array
     */
    protected function getSearchBetweenParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['between'] = $get['between'] ?? [];
        $where = [];
        foreach ($get['between'] as $key => $between) {
            list($from, $to) = [
                $this->formatSearchVal($key, $between['from'] ?? ''),
                $this->formatSearchVal($key, $between['to'] ?? ''),
            ];
            if ($from === '__undefined__') {
                $from = null;
            }
            if ($to === '__undefined__') {
                $to = null;
            }

            if (empty($from) && empty($to)) {
                continue;
            }

            if (false !== strpos($key, ',')) {
                list($fromKey, $toKey) = explode(',', $key);
                // list($fromKey) = $this->formatKey($fromKey , null);
                // list($toKey) = $this->formatKey($toKey , null);
                if (empty($toKey) || empty($fromKey)) {
                    continue;
                }
                list($from) = $this->datetime2integer($fromKey, $from, null);
                list(, $to) = $this->datetime2integer($toKey, null, $to);
                if (!empty($from)) {
                    $where[] = [$fromKey, '>=', $from];
                }
                if (!empty($to)) {
                    $where[] = [$toKey, '<=', $to];
                }
            } else {
                list($from, $to) = $this->datetime2integer($key, $from, $to);
                // list($from) = $this->formatKey($from , null);
                // list($to) = $this->formatKey($to,null);
                if (empty($from) || empty($to)) {
                    continue;
                }
                if (!empty($from) && !empty($to)) {
                    //$modelBuilder->whereBetween($key, [$from, $to]);
                    //continue;
                }
                if (!empty($from)) {
                    $where[] = [$key, '>=', $from];
                }
                if (!empty($to)) {
                    $where[] = [$key, '<=', $to];
                }
            }
        }
        return $where;
    }


    /**
     * 可能需要把datetime转换integer
     * @param $key
     * @param $from
     * @param $to
     * @return array
     */
    protected function datetime2integer($key, $from, $to)
    {
        $table_prefix = config('database.prefix');
        $db_name = config('database.database');
        $table_name = $table_prefix . table_name($this->getModelClass());
        $sql = "select COLUMN_TYPE as `c_type` from information_schema.COLUMNS where TABLE_SCHEMA=? and TABLE_NAME=? and COLUMN_NAME=?";
        $data = \DB::selectOne($sql , [$db_name , $table_name , $key]);
        if (empty($data)){
            $typeName = 'timestamp';
        }else{
            if (strpos($data->c_type ,'timestamp')!==false){
                $typeName = 'timestamp';
            }else{
                $typeName = '';
            }
        }
        if ($typeName == 'timestamp') {
            if (!empty($from) && !is_numeric($from)) {
                $from = $from . ' 00:00:00';
            }
            if (!empty($to) && !is_numeric($to)) {
                if (strpos($to, ':') !== false) {
                    $to = $to;
                } else {
                    $to = $to . ' 23:59:59';
                }
            }
        }
        return [$from, $to];
    }

    protected function formatKey($key , $value)
    {
        if (!preg_match_all("#^([a-zA-Z_\d]+)$#i", trim($key))) {
            return [false , $value];
        }
        return [$key , $value];
    }

    /**
     * 搜索条件。构造准确的值
     * @return array
     */
    protected function getSearchWhereParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['where'] = $get['where'] ?? [];
        $where = [];
        foreach ($get['where'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            $value = $this->formatSearchVal($key, $value);

            list($key , $value) = $this->formatKey($key,$value);
            if (empty($key)) {
                continue;
            }
            if ($value !== '') {
                $where[] = [$key, '=', $value];
            }
        }
        return $where;
    }


    protected function builderWhereArray()
    {
        return array_merge(
            $this->getSearchWhereParam(),
            $this->getSearchLikeParam(),
            $this->getSearchBetweenParam(),
            $this->getSearchDoubleLikeParam()
        );
    }

    protected $_setPost = [];

    /**
     * 过滤 post数据，
     * @param null $setPost
     * @return mixed
     */
    protected function postArray($setPost = null)
    {
        if ($setPost !== null) {
            $this->_setPost = $setPost;
            $post = $this->_setPost;
        }
        if (empty($post)) {
            $post = $_POST;
        }

        return $post;
    }

    protected function convertAff2Num(&$array, $keys, callable $callback)
    {
        $whereParams = ['search', 'where'];
        foreach ($whereParams as $name) {
            if (isset($array[$name]) && is_array($array[$name])) {
                foreach ($array[$name] as $k => &$v) {
                    if (in_array($k, $keys)) {
                        $v = get_num($v);
                    }
                }
            }
        }
    }


    protected function listAjaxOrder()
    {
        $array = $_GET['orderBy'] ?? [];
        $orderBy = [];
        foreach ($array as $key => $item) {
            if($key == 'custormsort_id'){
                if(!empty($item))$orderBy[$item] = 'desc';
            }
            else if (in_array($item, ['asc', 'desc'])) {
                $orderBy[$key] = $item;
            }
        }
        return $orderBy;
    }


    /**
     * 获取列表数据
     */
    public function listAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->ajaxError('加载错误');
        }

        // \DB::enableQueryLog();
        $pkName = $this->getPkName();
        /** @var \Illuminate\Database\Eloquent\Builder $modelBuilder */
        $modelBuilder = $this->getModelObject();
        $orderBy = $this->listAjaxOrder();
        if (empty($orderBy)) {
            $modelBuilder->orderBy($pkName, 'desc');
        } else {
            foreach ($orderBy as $column => $direction) {
                $modelBuilder->orderBy($column, $direction);
            }
        }

        $where = array_merge(
            $this->builderWhereArray(),
            $this->listAjaxWhere()
        );
        
        if (!empty($where)) {
            $modelBuilder->where($where);
        }

        /** @var \Illuminate\Database\Eloquent\Model $modelBuilder */

        list($limit, $offset) = self::limitOffsetByGet();
        $oldBuilder = clone $modelBuilder;
        $modelBuilder->limit($limit)->offset($offset);
        $this->whereSelectBefore($modelBuilder);
        $data = $modelBuilder->get()->map($this->listAjaxIteration());

        $result = [
            'count' => empty($data) ? 0 : $data->count(),
            'data'  => $data,
            "msg"   => '',
            "desc"  => $this->getDesc($oldBuilder),
            'code'  => 0
        ];
        // trigger_logger(\DB::getQueryLog());
        return $this->ajaxReturn($result);
    }

    protected function whereSelectBefore(&$query){

    }

    /** @var \Illuminate\Database\Eloquent\Model $modelBuilder */
    protected function getDesc($query):string{
        return '';
    }

    /**
     * 保存数据
     * @return bool
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        try {
            if ($model = $this->doSave($post)) {
                return $this->ajaxSuccessMsg('操作成功', 0, call_user_func($this->listAjaxIteration(),$model));
            } else {
                return $this->ajaxError('操作错误');
            }
        } catch (\Throwable $e) {
            trigger_log($e);
            return $this->ajaxError($e->getMessage());
        }
    }


    protected function createAfterCallback($model)
    {

    }

    protected function createBeforeCallback($model)
    {

    }

    protected function updateAfterCallback($model)
    {

    }

    protected function saveBeforeCallback($model){

    }

    protected function saveAfterCallback($model , $oldModel = null)
    {

    }


    protected function deleteAfterCallback($model ,$isDelete)
    {

    }


    /**
     * 删除数据
     * 后台全局公共方法 MetasModel CategoriesModel
     * @return mixed
     */
    public function delAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $className = $this->getModelClass();
        $pkName = $this->getPkName();
        $where = [$pkName => $post['_pk']];
        $model = $className::where($where)->first();
        $oldModel = clone  $model;

        if (empty($model) || $model->delete()) {
            $this->deleteAfterCallback($oldModel , true);
            return $this->ajaxSuccessMsg('操作成功');
        } else {
            $this->deleteAfterCallback($model , false);
            return $this->ajaxError('操作错误');
        }
    }

    /**
     * 删除数据
     * 后台全局公共方法
     * @return mixed
     */
    public function delAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $pkName = $this->getPkName();
        $ary = explode(',', $post['value'] ?? '');

        try {
            \DB::beginTransaction();
            foreach ($ary as $id) {
                if (empty($id)) {
                    continue;
                }
                $where = [$pkName => $id];
                $model = $this->getModelObject()->where($where)->first();
                $oldModel = clone $model;
                if (empty($model) || !$model->delete()) {
                    $this->deleteAfterCallback($oldModel , false);
                    throw new \Exception('删除失败');
                }
                $this->deleteAfterCallback($oldModel , true);
            }
            \DB::commit();
            return $this->ajaxSuccessMsg('操作成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->ajaxError('操作错误');
        }
    }

    protected function getModelObject(){
        $class = $this->getModelClass();
        return $class::query();
    }


    protected function doIfUpdate()
    {
        $data = $this->getRequest()->getPost();
        $data['_pk'] = $data['_pk'] ?? '';
        return empty($data['_pk']) ? false : $data['_pk'];
    }

    /**
     * 爆粗数据的操作
     * @param $data
     * @return bool
     */
    protected function doSave($data)
    {
        $className = $this->getModelClass();
        $pkName = $this->getPkName();
        $data['_pk'] = $data['_pk'] ?? '';
        /** @var \Illuminate\Database\Eloquent\Model $model */

        foreach ($data as $key => $value) {
            if (method_exists($this, 'set' . $key)) {
                $data[$key] = $this->{"set$key"}($value, $data, $data['_pk']);
                if ($data[$key] === null) {
                    unset($data[$key]);
                }
            }
        }

        if (empty($data['_pk'])) {
            $model = $className::make($data);
            $this->createBeforeCallback($model);
            $k = $model->save();
            $this->createAfterCallback($model);
            $this->saveAfterCallback($model);
            if (empty($k)) {
                throw new \RuntimeException('数据添加失败');
            }
        } else {
            $where = [[$pkName, '=', $data['_pk']]];
            $model = $className::where($where)->first();
            if (empty($model)) {
                throw new \RuntimeException('数据不存在');
            }
            try {
                $oldModel = clone $model;
                $model->fill($data)->saveOrFail();
                $this->updateAfterCallback($model);
                $this->saveAfterCallback($model, $oldModel);
            } catch (\Throwable $e) {
                $this->updateAfterCallback(null,null);
                $this->saveAfterCallback(null,null);
                throw $e;
            }
        }
        return $model;
    }

    /**
     * 获取对应的model名称
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    abstract protected function getPkName(): string;


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
        list($limit, $offset) = self::_limitOffset($_GET, $offsetName, $limitName);
        return $builder->offset($offset * $limit)->limit($limit);
    }


    /**
     * 返回分页参数
     * @param string $offsetName
     * @param string $limitName
     * @param int $defaultLimit
     * @return array [每页条数, offset , 第多少页]
     */
    public static function limitOffsetByGet($offsetName = 'page', $limitName = 'limit', $defaultLimit = 20)
    {
        return self::_limitOffset($_GET, $offsetName, $limitName, $defaultLimit);
    }


    /**
     * 返回分页参数
     * @param $data
     * @param string $offsetName
     * @param string $limitName
     * @param int $defaultLimit
     * @return array [每页条数, offset , 第多少页]
     */
    protected static function _limitOffset($data, $offsetName = 'page', $limitName = 'limit', $defaultLimit = 20)
    {
        //当前多少页
        $page = $data[$offsetName] ?? 1;
        $page = $page <= 1 ? 0 : $page - 1;
        //每页限时
        $limit = (int)($data[$limitName] ?? $defaultLimit);
        $limit = $limit <= 0 ? $defaultLimit : $limit;
        return [$limit, $page * $limit, $page + 1];
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