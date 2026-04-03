<?php

/**
 * @property string $id
 * @property string $aff
 * @property string $girl_chat_order_id
 * @property string $girl_chat_id
 * @property string $types
 * @property string $content
 * @property string $reason
 * @property string $img
 * @property string $status
 * @property string $ip_str
 * @property string $city_name
 * @property string $created_at
 * @property string $updated_at
 */
class GirlChatComplaintModel extends BaseModel
{
    protected $table = 'girl_chat_complaint';

    protected $fillable = [
        'id',
        'aff',
        'girl_chat_order_id',
        'girl_chat_id',
        'types',
        'content',
        'reason',
        'img',
        'status',
        'ip_str',
        'city_name',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    protected $appends = ["type_arr"];

    const OPTION_TYPE_1 = 1;
    const OPTION_TYPE_2 = 2;
    const OPTION_TYPE_3 = 3;
    const OPTION_TYPE_4 = 4;
    const OPTION_TYPE_5 = 5;
    const OPTION_TYPE_6 = 6;
    const OPTION_TYPE_7 = 7;
    const OPTION_TYPE_8 = 8;

    const OPTION_TYPE = [
        self::OPTION_TYPE_1 => "照片与本人不符",
        self::OPTION_TYPE_2 => "收定金",
        self::OPTION_TYPE_3 => "服务太差",
        self::OPTION_TYPE_4 => "被骗代聊",
        self::OPTION_TYPE_5 => "中途溜走",
        self::OPTION_TYPE_6 => "环境不好",
        self::OPTION_TYPE_7 => "虚假信息",
        self::OPTION_TYPE_8 => "其他投诉",
    ];

    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_FAILURE = 2;
    const STATUS = [
        self::STATUS_WAIT    => '待审核',
        self::STATUS_PASS    => '已通过',
        self::STATUS_FAILURE => '已拒绝'
    ];

    public function user()
    {
        return $this->belongsTo(MemberModel::class, "aff", "aff");
    }

    public function girlChat()
    {
        return $this->belongsTo(GirlChatModel::class, "girl_chat_id", "id");
    }

    public function getImgAttribute()
    {
        $value = $this->attributes['img'] ?? '';
        if (!empty($value)) {
//            $value = json_decode($value, true);
            $value = explode(",", $value);

            $result = [];
            foreach ($value as $img)
            {
                $result[] = url_image(parse_url($img, PHP_URL_PATH));
            }

            return $result;
        }
        return [];
    }

    public function getTypeArrAttribute()
    {
        $value = $this->attributes['types'] ?? '';
        if(!empty($value)){
            $value_arr = explode(",", $value);
            $return = [];
            if($value_arr){
                foreach ($value_arr as $item)
                {
                    $return[$item] = self::OPTION_TYPE[$item] ?? "";
                }
            }

            return $return;
        }

        return [];
    }

}
