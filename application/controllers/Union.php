<?php
/*
 * 联盟接入 逻辑处理
 */

use service\UserService;

class UnionController extends \Yaf\Controller_Abstract
{

    public $post = [];

    const SIGN_KEY = 'c8417b3340231faa5eaa91c5a2';
    const ENCRYPT_KEY = '9baef608fc88b2068dc38e';

    public function init()
    {
        //得到数据之前。需要对post数据解密，解密方式使用 LibCrypt类进行处理
        $rowInput = file_get_contents("php://input");
        parse_str($rowInput , $_POST);
        $_POST = $this->crypt()->checkInputData($_POST, false);
        $this->post = $_POST;
        error_log("channelRequest:".var_export($this->post,true));
    }

    protected function crypt(){
        $crypt = new LibCrypt();
        $crypt->setKey(self::SIGN_KEY, self::ENCRYPT_KEY);
        return $crypt;
    }


    public function indexAction()
    {
        $type = $this->post['gateway'] ?? 'member';
        switch ($type) {
            case 'channel':
                return $this->bindChanel();
            case 'sync.domain':
                return $this->syncDomain();
            default:
                return $this->showJson([],1,'no action match');
        }
    }
    /**
     * 同步域名
     */

    public function syncDomain()
    {
        $data = ['domain' => UserService::getShareURL()];
        return $this->showJson($data,1,'',true);
    }

    /**
     * 创建渠道
     */
    protected function bindChanel()
    {
        $data = $this->post;
        $agent_id = trim($data['agent_id'] ?? '');
        $agent_user = trim($data['agent_user'] ?? '');
        $agent_name = trim($data['agent_name'] ?? '');
        $parent_channel = trim($data['parent_channel'] ?? '');
        /** @var ChannelModel $parentRow */
        $parentRow = null;
        if ($parent_channel) {
            $parentRow = ChannelModel::where(['channel_num' => $parent_channel, 'agent_level' => 1,])->first();
            if (is_null($parentRow)) {
                return $this->showJson([], 0, '无效父级渠道标识#' . $parent_channel);
            }
        }
        /** @var ChannelModel $channel */
        $channel = ChannelModel::query()->where('channel_id', $agent_user)->first();
        if (empty($channel) && in_array(strtolower($agent_user), ['self', 'android', 'ios', 'pwa'])) {
            return $this->showJson([], 0, $agent_user . '为系统保留');
        }

        if (empty($channel)) {
            $member = new MemberModel();
            $member->oauth_type = 'channel';
            $member->oauth_id = md5($agent_id . TIMESTAMP);
            $member->username = $agent_user;
            $member->uuid = md5('channel' . md5($agent_id . TIMESTAMP));
            $member->role_id = MemberModel::ROLE_CHANNEL;
            $member->regdate = \Carbon\Carbon::now()->toDateTimeString();
            $member->channel = $agent_user;
            $member->build_id = 1;
            $member->regip = USER_IP;
            $member->save();
            $member->aff = $member->uid;
            $member->save();

            //新增渠道
            if ($parentRow) {
                $agent_id = $parentRow->channel_num;
            }
            $channel_data = array(
                'channel_num' => $agent_id,
                'channel_id' => $agent_user,
                'name' => $agent_name,
                'rate' => 0.5,
                'aff' => $member->aff,
                'status' => 1,
            );
            $channel_data['parent_channel'] = $parentRow ? $parentRow->channel_num : 1;
            $channel_data['agent_level'] = $parentRow ? $parentRow->agent_level + 1 : 1;
            $channel = ChannelModel::create($channel_data);
        }
        $aff_num = generate_code($channel->aff);
        $baseUrl = trim(UserService::getShareURL(), '/');
        $share_url = $baseUrl."/aff-$aff_num";
        $return = array(
            'product_chan' => $channel->channel_num,
            'product_chan_link' => $share_url,
            'extend' => [],
            'aff' => $channel->aff
        );
        return $this->showJson($return);
    }

    /**
     * 返回的数据需要加密
     * @return bool
     */
    public function showJson($data, $status = 1, $msg = '')
    {
        error_log("Respons".var_export($data,true));
        $returnData = $this->crypt()->replyData(['data'=>$data]);
        return $this->getResponse()->setBody($returnData);
    }

}