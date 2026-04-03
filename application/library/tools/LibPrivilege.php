<?php

namespace tools;


use ProductPrivilegeModel as Privilege;
use UserPrivilegeModel;

/**
 * @method __IDEPrivilegeResource mv()
 * @method __IDEPrivilegeResource vlog()
 * @method __IDEPrivilegeResource cartoon()
 * @method __IDEPrivilegeResource book()
 * @method __IDEPrivilegeResource story()
 * @method __IDEPrivilegeResource pic()
 * @method __IDEPrivilegeResource girl()
 * @method __IDEPrivilegeResource girlAgent()
 * @method __IDEPrivilegeResource girlChat()
 * @method __IDEPrivilegeResource system()
 * @method __IDEPrivilegeResource pua()
 */
class LibPrivilege
{
    public const  RESOURCE_METHOD_MAP = [
        'mv' => Privilege::RESOURCE_TYPE_LONG_VIDEO, // 视频资源
        'vlog' => Privilege::RESOURCE_TYPE_SHORT_VIDEO, //短视频资源
        'cartoon' => Privilege::RESOURCE_TYPE_CARTOON_VIDEO, //动漫
        'book' => Privilege::RESOURCE_TYPE_BOOK, // 漫画
        'story' => Privilege::RESOURCE_TYPE_STORY, // 小说
        'pic' => Privilege::RESOURCE_TYPE_PIC, // 图片
        'girl' => Privilege::RESOURCE_TYPE_GIRL, // 招嫖
        'girlAgent' => Privilege::RESOURCE_TYPE_GIRL_AGENT, // 上门
        'girlChat' => Privilege::RESOURCE_TYPE_GIRL_CHAT, //裸聊
        'system' => Privilege::RESOURCE_TYPE_SYSTEM, // 系统
        'pua' => Privilege::RESOURCE_TYPE_PUA, // pua
    ];

    public const TYPE_METHOD_MAP = [
        'view' => Privilege::PRIVILEGE_TYPE_VIEW,
        'down' => Privilege::PRIVILEGE_TYPE_DOWNLOAD,
        'comment' => Privilege::PRIVILEGE_TYPE_COMMENT,
        'discount' => Privilege::PRIVILEGE_TYPE_DISCOUNT,
        'unlock' => Privilege::PRIVILEGE_TYPE_UNLOCK,
        'setting' => Privilege::PRIVILEGE_TYPE_SETTING,
        'feed' => Privilege::PRIVILEGE_TYPE_FEED,
    ];

    /**
     * @var array
     * 预期数据
     * ```php
     * [
     *     int:资源类型1 => [
     *          int:权限类型1 => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     *          int:权限类型2 => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     *     ],
     *     int:资源类型2 => [
     *          int:权限类型1 => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     *          int:权限类型2 => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     *     ]
     * ]
     * ```
     */
    protected $data;
    protected $member;

    public function __construct(?\MemberModel $member , array $data)
    {
        $this->member = $member;
        $this->setData($data);
    }


    public function setData($data)
    {
        $this->data = $data;
    }


    public function __call($name, $arguments)
    {
        $method = LibPrivilege::RESOURCE_METHOD_MAP;
        if (isset($method[$name])) {
            $index = $method[$name];
            return $this->resource($index);
        }
        trigger_error("Call to undefined method tools\\LibPrivilege::$name()", E_USER_ERROR);
    }


    public function resource($resource_type): __IDEPrivilegeResource
    {
        return new __IDEPrivilegeResource($this, $this->data[$resource_type] ?? []);
    }

    public function clean()
    {
        cached(UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE . $this->member->aff)->clearCached();
    }


}

/**
 *
 * @method __IDEPrivilegeResourceType view()
 * @method __IDEPrivilegeResourceType down()
 * @method __IDEPrivilegeResourceType comment()
 * @method __IDEPrivilegeResourceType discount()
 * @method __IDEPrivilegeResourceType unlock()
 * @method __IDEPrivilegeResourceType setting()
 * @method __IDEPrivilegeResourceType feed()
 *
 *
 * @method int|false viewValue()
 * @method int|false downValue()
 * @method int|false commentValue()
 * @method int|false discountValue()
 * @method int|false settingValue()
 * @method int|false feedValue()
 * @method int|false unlockValue()
 *
 * @method bool viewDeduct($value=1)
 * @method bool downDeduct($value=1)
 * @method bool commentDeduct($value=1)
 * @method bool discountDeduct($value=1)
 * @method bool settingDeduct($value=1)
 * @method bool feedDeduct($value=1)
 * @method bool unlockDeduct($value=1) 扣除次数
 *
 * @method viewVerify(string $errMsg)
 * @method downVerify(string $errMsg)
 * @method commentVerify(string $errMsg)
 * @method discountVerify(string $errMsg)
 * @method unlockVerify(string $errMsg)
 * @method settingVerify(string $errMsg)
 * @method feedVerify(string $errMsg)
 *
 * @method bool viewHas()
 * @method bool downHas()
 * @method bool commentHas()
 * @method bool discountHas()
 * @method bool unlockHas()
 * @method bool settingHas()
 * @method bool feedHas()
 */
class __IDEPrivilegeResource
{
    /** @var LibPrivilege */
    protected $that;

    /**
     * @var array
     * 预期类型
     * ```php
     * [
     *     权限类型 => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     *     Privilege::PRIVILEGE_TYPE* => ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}],
     * ]
     * ```
     */
    protected $data;

    public function __construct($that, $data)
    {
        $this->that = $that;
        $this->data = $data;
    }

    private function getData(int $type): array
    {
        if (!is_array($this->data)) {
            return [];
        }
        return $this->data[$type] ?? [];
    }

    public function __call($name, $arguments)
    {
        $map = LibPrivilege::TYPE_METHOD_MAP;
        if (isset($map[$name])) {
            $index = $map[$name];
            return new __IDEPrivilegeResourceType($this->getData($index), $this->that);
        }

        if ($pos = strpos($name, 'Verify')) {
            $subMethod = 'verify';
        } elseif ($pos = strpos($name, 'Has')) {
            $subMethod = 'has';
        } elseif ($pos = strpos($name, 'Value')) {
            $subMethod = 'value';
        } elseif ($pos = strpos($name, 'Deduct')) {
            $subMethod = 'deduct';
        }
        if ($pos === false || !isset($subMethod)) {
            trigger_error("Call to undefined method __IDEPrivilegeResource::$name()", E_USER_ERROR);
        }
        $name = substr($name, 0, $pos);
        if (!isset($map[$name])) {
            trigger_error("Call to undefined method __IDEPrivilegeResource::$name()", E_USER_ERROR);
        }
        $index = $map[$name];
        $object = new __IDEPrivilegeResourceType($this->getData($index) , $this->that);
        return call_user_func_array([$object, $subMethod], $arguments);
    }
}

class __IDEPrivilegeResourceType
{
    /**
     * 预期类型
     * ```php
     * ["value"=>0 , "status"=>0, "_id"=>{user_privilege.id}]
     * ```
     * @var array
     */
    protected $data;
    /** @var LibPrivilege */
    protected $that;

    public function __construct($data , $that)
    {
        $this->data = $data;
        $this->that = $that;
    }

    public function verify($msg)
    {
        if (!$this->has()) {
            throw new \RuntimeException($msg);
        }
        return true;
    }

    public function value()
    {
        return $this->data['value'] ? intval($this->data['value']) : false;
    }

    public function has(): bool
    {
        if (!isset($this->data) || !isset($this->data['status']) || !isset($this->data['value'])) {
            return false;
        }
        return $this->data['status'] == 1;
    }

    /** 扣除次数 */
    public function deduct($value = 1): bool
    {
        // deduct
        if (!$this->has()) {
            return false;
        }
        if ($this->value() >= 9999) {
            return true;
        }
        $id = $this->data['_id'] ?? 0;
        if (empty($id)) {
            return false;
        }
        $isOk = \UserPrivilegeModel::where('id', $id)->decrement('value', $value) > 0;
        if ($isOk){
            $this->that->clean();
        }
        return  $isOk;
    }
}