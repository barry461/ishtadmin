<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use tools\LibCollection;

/**
 * Class BaseModel
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model implements Serializable
{
    use \traits\EventLog;
    /** @var array<static> */
    protected $joinModels = [];
    protected $virtualAttributes = [];

    /**
     * 将链试调用转换成 \Closure 用来提供给 yac()->fetch(), redis()->getx(), cahced()->fetch()
     * 试验性代码，自行选择使用
     * @param ...$args
     * @return tools\LibClosure|static|object|self|Eloquent
     */
    public static function closure(...$args): \tools\LibClosure
    {
        return \tools\LibClosure::new(static::queryBase(...$args));
    }

    protected function bootIfNotBooted()
    {
        if (self::$dispatcher === null) {
            self::$dispatcher = new Dispatcher();
        }
        parent::bootIfNotBooted();
    }

    public function removeHidden(...$attrs): BaseModel
    {
        $attrs = is_array(func_get_arg(0)) ? func_get_arg(0) : $attrs;
        $this->hidden = array_diff($this->hidden, $attrs);
        return $this;
    }

    public function getCreatedAtAttribute($value): string
    {
        if ($value === '0000-00-00 00:00:00') {
            $value = '1970-01-01 00:00:01';
        }
        if ($value == 0 || $value === null || $value === '') {
            return '';
        }

        // 避免依赖 Carbon，直接用原生函数格式化
        if (is_numeric($value)) {
            $timestamp = (int)$value;
        } else {
            $timestamp = strtotime((string)$value);
        }

        if ($timestamp <= 0) {
            return '';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    public function getUpdatedAtAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    /**
     * 重写 fromDateTime，避免调用 Carbon 的 format()
     */
    public function fromDateTime($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}/', $value)) {
                return $value;
            }
            $timestamp = strtotime($value);
            if ($timestamp > 0) {
                return date($this->getDateFormat(), $timestamp);
            }
            return $value;
        }
        
        if (is_numeric($value)) {
            return date($this->getDateFormat(), (int)$value);
        }
        
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->getDateFormat());
        }
        
        return date($this->getDateFormat());
    }
    
    /**
     * 重写 freshTimestampString，避免使用 Carbon
     */
    public function freshTimestampString()
    {
        return date($this->getDateFormat());
    }

    public static function queryBase(...$args)
    {
        $query = self::query();
        if (count($args)) {
            return $query->where(...$args);
        }
        return $query;
    }

    /**
     * @param array $models
     * @return LibCollection|object
     */
    public function newCollection(array $models = [])
    {
        return new LibCollection($models);
    }


    /**
     * @var array|MemberModel 观察数据的用户，是哪个用户在对数据进行观察
     */
    protected static $watchUser = null;

    public static function setWatchUser(?MemberModel $watchUser)
    {
        self::$watchUser = $watchUser;
    }


    /**
     * @param ?MemberModel $watchUser
     *
     * @return static|object
     */
    public function watchByUser(?MemberModel $watchUser)
    {
        self::setWatchUser($watchUser);
        return $this;
    }

    public function getJoinModels()
    {
        return $this->joinModels;
    }

    /**
     * @return static|object
     */
    public function joinModel(...$args)
    {
        foreach ($args as $arg) {
            if ($arg instanceof self) {
                $this->joinModels[] = $arg;
            }
        }
        return $this;
    }

    /**
     * 重写 addDateAttributesToArray，避免 Carbon 触发 Symfony 翻译接口检查
     */
    protected function addDateAttributesToArray(array $attributes)
    {
        // 获取所有日期字段（getDates 已经包含了 created_at 和 updated_at）
        $dates = $this->getDates();
        
        foreach ($dates as $key) {
            if (!isset($attributes[$key])) {
                continue;
            }
            
            $value = $attributes[$key];
            
            // 如果已经是字符串格式（Y-m-d H:i:s），保持不变
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}/', $value)) {
                continue;
            }
            
            // 转换为字符串格式，避免使用 Carbon
            if ($value === null || $value === '') {
                $attributes[$key] = '';
            } elseif (is_numeric($value)) {
                $timestamp = (int)$value;
                if ($timestamp > 0) {
                    $attributes[$key] = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $attributes[$key] = '';
                }
            } else {
                $timestamp = strtotime((string)$value);
                if ($timestamp > 0) {
                    $attributes[$key] = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $attributes[$key] = '';
                }
            }
        }
        
        return $attributes;
    }

    public function toArray(): array
    {
        $ary = parent::toArray();
        foreach ($this->joinModels as $joinModel) {
            $ary = array_merge($ary, $joinModel->toArray());
        }
        if (is_array($this->virtualAttributes)) {
            return array_merge($ary, $this->virtualAttributes);
        }
        return $ary;
    }


    /**
     * @return int 返回下一次入库的id
     */
    public static function next_insert_id(): int
    {
        $connection = self::query()->getQuery()->connection;
        $sql
            = "select AUTO_INCREMENT as next_id from information_schema.TABLES where TABLE_SCHEMA=? and TABLE_NAME=?;";
        $table_name = ($connection->getTablePrefix() ?? '') . self::getModel()
                ->getTable();
        $data = DB::selectOne($sql,
            [$connection->getDatabaseName(), $table_name], false);

        return intval($data->next_id ?? 1);
    }


    /**
     * @param array $attributes
     *
     * @return static|object
     */
    public static function makeOnce(array $attributes)
    {
        $model = static::make();
        $model->exists = true;
        $model->setRawAttributes($attributes, true);

        return $model;
    }

    /**
     * 将二维数组还原成model对象
     *
     * @param array<array> $ary
     * @param bool $sync
     *
     * @return \Illuminate\Support\Collection|static[]
     */
    public static function makeCollect(array $ary, bool $sync = true)
    {
        $model = static::make();
        $models = [];
        foreach ($ary as $item) {
            if (empty($item)) {
                continue;
            }
            $object = clone $model;
            $object->exists = true;
            $object->setRawAttributes($item, $sync);
            $models[] = $object;
        }
        unset($object, $item, $ary);
        return $model->newCollection($models);
    }

    /**
     * 如果置顶属性为空，就设置到置顶的值
     *
     * @param string $attrName
     * @param $val
     *
     * @return static|object
     */
    public function emptySet(string $attrName, $val)
    {
        if (empty($this->{$attrName})) {
            $this->{$attrName} = $val;
        }

        return $this;
    }

    /**
     * 如果置顶属性为空，就设置到置顶的值
     *
     * @param mixed $val
     * @param string $attrName
     * @param mixed $data
     *
     * @return static|object
     */
    public function whenSet($val, string $attrName, $data = null)
    {
        if (!empty($val)) {
            $this->{$attrName} = $data ?? $val;
        }
        return $this;
    }

    public function serialize(): string
    {
        return serialize([
            $this->attributes,
            $this->relations,
            $this->joinModels,
        ]);
    }

    public function unserialize($data)
    {
        list($attr, $rel) = $arr = unserialize($data);
        $this->relations = $rel;
        $this->joinModels = $arr[2] ?? [];
        $this->setRawAttributes($attr, true);
        $this->exists = true;

        return $this;
    }

    public function resetSetPathAttribute(string $string, $value)
    {
        $old = $this->getOriginal($string);
        if (empty($old) && empty($value)) {
            $this->attributes[$string] = $value;
            return;
        }
        if (!empty($value) && strpos($value, '://') !== false) {
            $value = parse_url($value, PHP_URL_PATH);
            $value = '/' . trim($value, '/');
        }
        $this->attributes[$string] = $value;
    }


    /**
     * 将 关联模型展开成为一个对象的attributes
     * @return $this|static|BaseModel
     */
    public function flattenRelation(): BaseModel
    {
        $ary = [];
        foreach ($this->relations as $name => $relation) {
            if ($relation instanceof BaseModel) {
                $ary = array_merge($ary, $relation->flattenRelation()->toArray());
            }
            unset($this->relations[$name]);
        }
        foreach ($ary as $key => $item) {
            if (!isset($this->attributes[$key])) {
                $this->attributes[$key] = $item;
            }
        }
        return $this;
    }

    protected function resolveConstantValue(array $constAry, string $key, $unknown = '未知')
    {
        return $constAry[$this->attributes[$key] ?? '-unknown-'] ?? $unknown;
    }

    public function appendVirtual($attributes){
        $attributes = is_string($attributes) ? func_get_args() : $attributes;
        foreach ($attributes as $item){
            $this->append($item.'_str');
        }
    }


    public static function selectShield($columns ){
        if (is_string($columns)){
            $columns = func_get_args();
        }
        $query = static::make();
        $columns = array_diff($query->fillable ,$columns );
        $columns = array_values($columns);
        return static::query()->select($columns);
    }

    public static function batchUpdate($data): int
    {
        if (empty($data)){
            return 0;
        }
        $tempModel = self::make();
        $pkName = $tempModel->primaryKey;
        $pkValues = array_column($data, $pkName);
        if (empty($pkValues) || count($pkValues) != count($data)) {
            throw new RuntimeException('数据中没有主建的值');
        }
        $connection = $tempModel->newQuery()->getConnection();
        $ref = new ReflectionObject($connection);
        $project = $ref->getProperty('tablePrefix');
        $project->setAccessible(true);
        $table = $project->getValue($connection).$tempModel->getTable();

        $tpl = 'UPDATE '.'${{table}} set ${{set}} WHERE ${{where}}';
        $inStr = trim(str_repeat('?,', count($pkValues)), ',');
        $where = "{$pkName} in (".$inStr.")";
        $bindings = [];
        $current = current($data);
        $ary = [];
        foreach ($current as $column => $_) {
            if ($column == $pkName) {
                continue;
            }
            $tmp = "`$column`=(CASE {$pkName} {WHEN} END)";
            $items = array_column($data, $column);
            $s = [];
            foreach ($pkValues as $k => $v) {
                $s[] = "WHEN ? THEN ?";
                $bindings[] = $v;
                $bindings[] = $items[$k];
            }
            $ary[] = str_replace('{WHEN}', join(' ', $s), $tmp);
        }
        if (empty($ary)) {
            throw new RuntimeException('不存在需要更新的值');
        }
        $bindings = array_merge($bindings , $pkValues);
        $set = join(',', $ary);
        $sql = str_replace(['${{table}}', '${{where}}', '${{set}}'], [$table, $where, $set], $tpl);
        return $connection->update($sql, $bindings);
    }


    public function fieldValue($name, $default = null)
    {
        return $default;
    }

    public function url(): ?ParseUrl
    {
        return null;
    }

    public function isPage()
    {
        $url = $this->url();
        return str_contains($_SERVER['REQUEST_URI'] , $url);
    }

}