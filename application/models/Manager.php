<?php

/**
 * 管理员model
 *
 * @property int uid
 * @property int oauth_type
 * @property int oauth_id
 * @property int uuid
 * @property int username
 * @property int password
 * @property int role_id
 * @property string role_type
 * @property string gender
 * @property string regip
 * @property int regdate
 * @property string lastip
 * @property int lastvisit
 * @property int expired_at
 * @property int lastpost
 * @property int oltime
 * @property int login_task
 * @property int pageviews
 * @property int score
 * @property string aff
 * @property int invited_by
 * @property int invited_num
 * @property int newpm
 * @property string|null nickname 显示昵称（与登录账号 username 分离）
 * @property int new_comment_reply
 * @property int new_topic_reply
 * @property string login_count
 * @property string app_version
 * @property int validate
 *
 *
 * @mixin \Eloquent
 */
class ManagerModel extends BaseModel
{

    protected $table = 'managers';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    protected $hidden = ['password'];

    protected $fillable = [
        'uid',
        'oauth_type',
        'oauth_id',
        'uuid',
        'username',
        'nickname',
        'password',
        'role_id',
        'role_type',
        'gender',
        'regip',
        'regdate',
        'lastip',
        'lastvisit',
        'expired_at',
        'lastpost',
        'oltime',
        'login_task',
        'pageviews',
        'score',
        'aff',
        'invited_by',
        'invited_num',
        'newpm',
        'new_comment_reply',
        'new_topic_reply',
        'login_count',
        'app_version',
        'validate',
        'secret'
    ];


    const ROLE_TYPE_ADMIN = 'admin';
    const ROLE_TYPE_NORMAL = 'normal';

    const ROLE_TYPE = [
        SELF::ROLE_TYPE_ADMIN  => '管理员',
        SELF::ROLE_TYPE_NORMAL => '普通',
    ];


    /**
     * @param $username
     * @return ManagerModel|null
     * @author xiongba
     * @date 2020-05-20 14:30:22
     */
    public static function login($username)
    {
        $model = self::where(['username' => $username])->first();
        if (empty($model)) {
            return null;
        }
        return $model;
    }

    public function isAdminRole()
    {
        if ($this->role_type == self::ROLE_TYPE_ADMIN) {
            return true;
        }
        return false;
    }

    public function verifyPassword($password)
    {
        if (self::makePasswordHash($password) == $this->password) {
            return true;
        }
        return false;
    }


    public function updateLoginStatus()
    {
        try {
     
        $this->lastvisit = time();
        $this->lastip = client_ip();
        $this->login_count += 1;
        $this->save();
           } catch (\Exception $e) {
            wf('ManagerModel:updateLoginStatus', [
                'uid' => $this->uid,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * 生成密码
     * @param string $password
     * @return string
     */
    public static function makePasswordHash(string $password)
    {
        return md5($password);
    }


}
