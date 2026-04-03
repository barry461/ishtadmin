<?php

/**
 * class MembersOldModel
 *
 *
 * @property int $uid
 * @property string $oauth_type 设备'ios','android'
 * @property string $oauth_id
 * @property string $oauth_new_id
 * @property string $uuid
 * @property string $username
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 * @property int $role_id
 * @property int $gender
 * @property string $regip
 * @property string $regdate
 * @property string $lastip
 * @property string $expired_at 会员到期时间
 * @property int $lastpost
 * @property int $oltime 在线小时数
 * @property int $pageviews 论坛用的,电影可以不用
 * @property int $score 用户积分
 * @property int $aff 唯一标示
 * @property string $channel
 * @property int $invited_by 被谁 aff 邀请
 * @property int $invited_num 已邀请安装个数
 * @property int $ban_post 1禁止发资源
 * @property int $post_num 一天最多发资源数
 * @property int $login_count
 * @property string $app_version app版本号
 * @property int $validate
 * @property int $share 分享
 * @property int $is_login
 * @property string $nickname 用户昵称
 * @property string $thumb 用户头像
 * @property string $coins 铜钱
 * @property string $money 元宝
 * @property string $proxy_money 代理收益
 * @property int $temp_vip 临时卡
 * @property int $followed_count 有多少人关注此用户
 * @property int $videos_count 作品数
 * @property int $fabulous_count 获赞数
 * @property int $likes_count 喜欢数
 * @property int $comment_count 视频被评论数
 * @property int $vip_level vip等级
 * @property string $person_signnatrue 个人签名
 * @property int $old_vip 生日
 * @property int $stature 身高
 * @property string $interest 爱好兴趣(此字段已用于嫌疑用户白名单)
 * @property string $city 城市
 * @property int $used_money_free_num 使用免费解锁元宝次数
 * @property int $agent_fee 经纪人扣点1为1%
 * @property int $agent 1经纪人2认证楼风
 * @property int $level
 * @property int $build_id build_id 超级签名标识
 * @property int $auth_status 0 未认证 1 认证通过
 * @property int $exp 积分
 * @property string $is_virtual 是否是虚拟用户
 * @property string $chat_uid
 * @property string $phone 手机
 * @property string $phone_prefix 手机地区号
 * @property int $free_view_cnt 剩余免费观看次数
 * @property string $lastactivity 最后活跃时间
 * @property int $income_total 提现
 * @property int $income_money 收益
 * @property string $draw_name 提现名字
 * @property int $ip_invite 是否通过ip进行的邀请
 * @property int $order_count 支付订单数量
 * @property int $post_count 帖子数量
 * @property int $topic_count 创建的剧集数量
 * @property int $follow_count 用户关注的数量
 * @property string $tags 用户打的标签 最多5个 用逗号分隔
 * @property string $free_view_date 剩余免费观看次数视效
 * @property int $invited_reg_num 操作失败
 * @property int $unread_reply 未读的评论回复
 * @property int $post_club_id 帖子配置详情
 * @property int $post_club_month 帖子订阅月卡价格
 * @property int $post_club_quarter 帖子订阅季卡价格
 * @property int $post_club_year 帖子订阅年卡价格
 * @property int $post_club_total 帖子订阅总收益
 * @property int $income_royalties 稿费余额
 * @property int $income_royalties_total 稿费总收益
 * @property string $email 邮箱地址
 *
 *
 *
 * @mixin \Eloquent
 */
class MembersOldModel extends BaseModel
{
    protected $table = 'members_old';
    protected $primaryKey = 'uid';
    protected $fillable = [
        'uid',
        'oauth_type',
        'oauth_id',
        'oauth_new_id',
        'uuid',
        'username',
        'password',
        'created_at',
        'updated_at',
        'role_id',
        'gender',
        'regip',
        'regdate',
        'lastip',
        'expired_at',
        'lastpost',
        'oltime',
        'pageviews',
        'score',
        'aff',
        'channel',
        'invited_by',
        'invited_num',
        'ban_post',
        'post_num',
        'login_count',
        'app_version',
        'validate',
        'share',
        'is_login',
        'nickname',
        'thumb',
        'coins',
        'money',
        'proxy_money',
        'temp_vip',
        'followed_count',
        'videos_count',
        'fabulous_count',
        'likes_count',
        'comment_count',
        'vip_level',
        'person_signnatrue',
        'old_vip',
        'stature',
        'interest',
        'city',
        'used_money_free_num',
        'agent_fee',
        'agent',
        'level',
        'build_id',
        'auth_status',
        'exp',
        'is_virtual',
        'chat_uid',
        'phone',
        'phone_prefix',
        'free_view_cnt',
        'lastactivity',
        'income_total',
        'income_money',
        'draw_name',
        'ip_invite',
        'order_count',
        'post_count',
        'topic_count',
        'follow_count',
        'tags',
        'free_view_date',
        'invited_reg_num',
        'unread_reply',
        'post_club_id',
        'post_club_month',
        'post_club_quarter',
        'post_club_year',
        'post_club_total',
        'income_royalties',
        'income_royalties_total',
        'email'
    ];
    protected $guarded = 'uid';
    public $timestamps = false;
}